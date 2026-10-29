<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_category_crud_are_available(): void
    {
        $category = Category::create(['name' => 'Components']);

        $this->get(route('materials.index'))->assertOk()->assertSee('Manage materials');
        $this->get(route('categories.index'))->assertOk()->assertSee('Components');
        $this->get(route('categories.create'))->assertOk()->assertSee('Add a category');
        $this->post(route('categories.store'), ['name' => 'Fasteners'])
            ->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Fasteners']);

        $this->put(route('categories.update', $category), ['name' => 'Parts'])
            ->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Parts']);

        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_material_crud_and_signed_movements_keep_current_balance_correct(): void
    {
        $category = Category::create(['name' => 'Raw material']);

        $this->post(route('materials.store'), [
            'category_id' => $category->id,
            'name' => 'Steel plate 10mm',
            'opening_balance' => '20.50',
        ])->assertRedirect(route('materials.index'));

        $material = Material::firstOrFail();
        $this->assertSame(1, $material->id);
        $this->get(route('materials.create'))->assertOk()->assertSee('Add a material');
        $this->get(route('materials.edit', $material))->assertOk()->assertSee('Edit material');

        $this->post(route('stock-movements.store'), [
            'category_id' => $category->id,
            'material_id' => $material->id,
            'movement_date' => '2026-10-29',
            'quantity' => '-3.25',
        ])->assertRedirect(route('stock-movements.index'));

        $movement = StockMovement::firstOrFail();
        $this->assertSame(1, $movement->id);
        $this->get(route('stock-movements.create'))->assertOk()->assertSee('Record stock movement');
        $this->get(route('stock-movements.edit', $movement))->assertOk()->assertSee('Edit stock movement');

        $this->get(route('materials.index'))
            ->assertOk()
            ->assertSee('17.25');

        $this->put(route('materials.update', $material), [
            'category_id' => $category->id,
            'name' => 'Steel plate 10mm',
            'opening_balance' => '10.00',
        ])->assertRedirect(route('materials.index'));

        $this->get(route('materials.index'))
            ->assertOk()
            ->assertSee('6.75');
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id, 'quantity' => '-3.25']);

        $this->put(route('stock-movements.update', $movement), [
            'category_id' => $category->id,
            'material_id' => $material->id,
            'movement_date' => '2026-10-30',
            'quantity' => '1.00',
        ])->assertRedirect(route('stock-movements.index'));

        $this->get(route('materials.index'))
            ->assertOk()
            ->assertSee('11.00');

        $this->delete(route('stock-movements.destroy', $movement))
            ->assertRedirect(route('stock-movements.index'));
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
        $this->get(route('materials.index'))
            ->assertOk()
            ->assertSee('10.00');
    }

    public function test_material_archive_preserves_history_and_can_be_restored(): void
    {
        $category = Category::create(['name' => 'Spares']);
        $material = Material::create([
            'category_id' => $category->id,
            'name' => 'Bearing 6201',
            'opening_balance' => '4.00',
        ]);
        $movement = StockMovement::create([
            'material_id' => $material->id,
            'movement_date' => '2026-10-29',
            'quantity' => '2.00',
        ]);

        $this->delete(route('materials.destroy', $material))
            ->assertRedirect(route('materials.index'));
        $this->assertSoftDeleted('materials', ['id' => $material->id]);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id]);

        $this->patch(route('materials.restore', $material->id))
            ->assertRedirect(route('materials.index'));
        $this->assertNotSoftDeleted('materials', ['id' => $material->id]);
    }

    public function test_categories_in_use_and_invalid_decimal_amounts_are_rejected(): void
    {
        $category = Category::create(['name' => 'Machines']);
        Material::create([
            'category_id' => $category->id,
            'name' => 'Lathe 2000',
            'opening_balance' => '1.00',
        ]);

        $this->delete(route('categories.destroy', $category))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $this->from(route('materials.create'))
            ->post(route('materials.store'), [
                'category_id' => $category->id,
                'name' => 'Drill bits',
                'opening_balance' => '1.234',
            ])
            ->assertSessionHasErrors('opening_balance');
        $this->assertDatabaseMissing('materials', ['name' => 'Drill bits']);

        $this->from(route('categories.create'))
            ->post(route('categories.store'), ['name' => 'Machines'])
            ->assertSessionHasErrors('name');

        $this->from(route('materials.create'))
            ->post(route('materials.store'), [
                'category_id' => $category->id,
                'name' => 'Invalid/Name',
                'opening_balance' => '1.00',
            ])
            ->assertSessionHasErrors('name');

        $this->from(route('materials.create'))
            ->post(route('materials.store'), [
                'category_id' => $category->id,
                'name' => 'Lathe 2000',
                'opening_balance' => '2.00',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_stock_movement_material_must_belong_to_selected_category(): void
    {
        $rawMaterials = Category::create(['name' => 'Raw material']);
        $spares = Category::create(['name' => 'Spares']);
        $material = Material::create([
            'category_id' => $rawMaterials->id,
            'name' => 'Copper wire',
            'opening_balance' => '3.00',
        ]);

        $this->from(route('stock-movements.create'))
            ->post(route('stock-movements.store'), [
                'category_id' => $spares->id,
                'material_id' => $material->id,
                'movement_date' => '2026-10-29',
                'quantity' => '1.00',
            ])
            ->assertSessionHasErrors('material_id');
        $this->assertDatabaseCount('stock_movements', 0);

        $this->from(route('stock-movements.create'))
            ->post(route('stock-movements.store'), [
                'category_id' => $rawMaterials->id,
                'material_id' => $material->id,
                'movement_date' => 'not-a-date',
                'quantity' => '1.00',
            ])
            ->assertSessionHasErrors('movement_date');
        $this->assertDatabaseCount('stock_movements', 0);

        $this->from(route('stock-movements.create'))
            ->post(route('stock-movements.store'), [
                'category_id' => $rawMaterials->id,
                'material_id' => $material->id,
                'movement_date' => '2026-10-29',
                'quantity' => '-1.234',
            ])
            ->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
