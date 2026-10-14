<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FilterDefinition;
use App\Services\AuditLogger;
use App\Services\CategoryTreeService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request, CategoryTreeService $tree)
    {
        $roots = $tree->roots();
        $categories = $tree->flatten($roots);
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $categories = $categories->filter(fn (Category $category) => str_contains(mb_strtolower($category->name.' '.$category->slug), mb_strtolower($search)))->values();
        }

        return view('admin.m06.categories.index', [
            'categories' => $categories,
            'category_roots' => $roots,
            'category_search' => $search,
            'category_options' => $tree->allForSelect(),
            'filter_definitions' => FilterDefinition::with('categories', 'values')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CategoryTreeService $tree, AuditLogger $audit)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $category = $tree->create($data);
        $audit->record('category.created', $category, ['name' => $category->name, 'parent_id' => $category->parent_id]);

        return back()->with('success', 'Kategória mentve.');
    }

    public function update(Request $request, int $id, CategoryTreeService $tree, AuditLogger $audit)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $category = $tree->update($category, $data);
        $audit->record('category.updated', $category, ['name' => $category->name, 'parent_id' => $category->parent_id]);

        return back()->with('success', 'Kategória frissítve.');
    }

    public function destroy(int $id, CategoryTreeService $tree, AuditLogger $audit)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $tree->delete($category);
        $audit->record('category.deleted', $category, ['name' => $name]);

        return back()->with('success', 'Kategória törölve.');
    }
}
