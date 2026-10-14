<?php

namespace App\Http\Controllers;

use App\Models\FilterDefinition;
use App\Services\AuditLogger;
use App\Services\FilterCatalogService;
use Illuminate\Http\Request;

class FilterDefinitionController extends Controller
{
    public function store(Request $request, FilterCatalogService $filters, AuditLogger $audit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:select,multi,boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ]);
        $definition = $filters->createDefinition($data);
        $audit->record('filter_definition.created', $definition, ['name' => $definition->name]);

        return back()->with('success', 'Szűrő és értékei mentve.');
    }

    public function update(Request $request, int $id, AuditLogger $audit)
    {
        $definition = FilterDefinition::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);
        $definition->update([
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        $definition->categories()->sync($data['category_ids'] ?? []);
        $audit->record('filter_definition.updated', $definition, ['name' => $definition->name]);

        return back()->with('success', 'Szűrő frissítve.');
    }

    public function destroy(int $id, AuditLogger $audit)
    {
        $definition = FilterDefinition::findOrFail($id);
        $name = $definition->name;
        $definition->delete();
        $audit->record('filter_definition.deleted', $definition, ['name' => $name]);

        return back()->with('success', 'Szűrő törölve.');
    }
}
