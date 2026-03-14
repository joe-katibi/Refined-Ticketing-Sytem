<?php
namespace Modules\Escalations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Escalations\Entities\Category;
use Modules\Escalations\Entities\Subcategory;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    // CATEGORY METHODS
    public function index()
    {
        $categories = Category::with(['subcategories', 'createdBy'])->paginate(10);
        return view('escalations::categories.index', compact('categories'));
    }
    public function create()
    {
        return view('escalations::categories.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $validated['created_by'] = Auth::user()->id;
        Category::create($validated);
        return redirect()->route('escalation-category.index')->with('success', 'Category created successfully.');
    }
    public function edit(Category $category)
    {
        return view('escalations::categories.edit', compact('category'));
    }
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $validated['edited_by'] = Auth::user()->id;
        $category->update($validated);
        return redirect()->route('escalation-category.index')->with('success', 'Category updated successfully.');
    }
    public function inactive(Category $category)
    {
        $category->status = 'Inactive';
        $category->edited_by = Auth::user()->id;
        $category->save();
        return redirect()->route('escalation-category.index')->with('success', 'Category inactivated successfully.');
    }
    public function active(Category $category)
    {
        $category->status = 'Active';
        $category->edited_by = Auth::user()->id;
        $category->save();
        return redirect()->route('escalation-category.index')->with('success', 'Category activated successfully.');
    }
    public function show(Category $category)
    {
        $category->load('subcategories');
        return view('escalations::categories.show', compact('category'));
    }

    // SUBCATEGORY METHODS
    public function subcategoryIndex(Category $category)
    {
        $subcategories = $category->subcategories()->paginate(10);
        return view('escalations::categories.subcategories.index', compact('category', 'subcategories'));
    }
    public function subcategoryCreate(Category $category)
    {
        return view('escalations::categories.subcategories.create', compact('category'));
    }
    public function subcategoryStore(Request $request, Category $category)
    {
        $validated = $request->validate([
            'sub_category_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $validated['created_by'] = Auth::user()->id;
        $validated['category_id'] = $category->id;
        Subcategory::create($validated);
        return redirect()->route('escalation-category.subcategories.index', $category)->with('success', 'Subcategory created successfully.');
    }
    public function subcategoryEdit(Category $category, Subcategory $subcategory)
    {
        return view('escalations::categories.subcategories.edit', compact('category', 'subcategory'));
    }
    public function subcategoryUpdate(Request $request, Category $category, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'sub_category_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
        $validated['edited_by'] = Auth::user()->id;
        $subcategory->update($validated);
        return redirect()->route('escalation-category.subcategories.index', $category)->with('success', 'Subcategory updated successfully.');
    }
    public function subcategoryInactive(Category $category, Subcategory $subcategory)
    {
        $subcategory->status = 'Inactive';
        $subcategory->edited_by = Auth::user()->id;
        $subcategory->save();
        return redirect()->route('escalation-category.subcategories.index', $category)->with('success', 'Subcategory inactivated successfully.');
    }
    public function subcategoryActive(Category $category, Subcategory $subcategory)
    {
        $subcategory->status = 'Active';
        $subcategory->edited_by = Auth::user()->id;
        $subcategory->save();
        return redirect()->route('escalation-category.subcategories.index', $category)->with('success', 'Subcategory activated successfully.');
    }
    public function subcategoryShow(Category $category, Subcategory $subcategory)
    {
        return view('escalations::categories.subcategories.show', compact('category', 'subcategory'));
    }
}
