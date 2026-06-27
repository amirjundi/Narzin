<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\ProductManagement\Models\Category;

class CategoryController extends Controller
{
    protected $maxLevel = 3; // Maximum allowed level for categories

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get only root categories with their descendants
        $categories = Category::whereNull('parent_id') // Load up to grandchildren
            ->get();
            return view('admin::categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')
            ->get();
        return view('admin::categories.create', compact('categories'));
    }

    /**
     * Get category level
     */
    private function getCategoryLevel($category)
    {
        $level = 1;
        while ($category->parent) {
            $level++;
            $category = $category->parent;
        }
        return $level;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name_arabic' => 'required|string|max:255',
                'name_german' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ])->validate();

            // Check category level
            if (isset($validatedData['parent_id'])) {
                $parentCategory = Category::find($validatedData['parent_id']);
                $level = $this->getCategoryLevel($parentCategory) + 1;
                
                if ($level > $this->maxLevel) {
                    return redirect()->back()
                        ->withErrors(['level' => 'Maximum category depth level exceeded'])
                        ->withInput();
                }
            }

            // Generate slugs
            $validatedData['slug_arabic'] = Str::slug($validatedData['name_arabic'], '-', 'ar');
            $validatedData['slug_german'] = Str::slug($validatedData['name_german']);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
                $validatedData['image'] = $imagePath;
            }
 
            Category::create($validatedData);

            return redirect()->route('categories.index')
                ->with('success', 'Category created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);
        return view('admin::categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::where('id', '!=', $id)
            ->whereNull('parent_id')
            ->with(['children'])
            ->get();
        return view('admin::categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'name_arabic' => 'required|string|max:255',
                'name_german' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ])->validate();

            $category = Category::findOrFail($id);



            // Generate new slugs
            $validatedData['slug_arabic'] = Str::slug($validatedData['name_arabic'], '-', 'ar');
            $validatedData['slug_german'] = Str::slug($validatedData['name_german']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $imagePath = $request->file('image')->store('categories', 'public');
                $validatedData['image'] = $imagePath;
            }

            $category->update($validatedData);

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            // Delete image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            // Delete category (children will be handled by foreign key constraint)
            $category->delete();

            return redirect()->route('categories.index')
                ->with('success', 'Category deleted successfully');
        } catch (Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }
}