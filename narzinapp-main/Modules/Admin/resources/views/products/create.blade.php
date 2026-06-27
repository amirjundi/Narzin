<x-admin-layout>
    <div x-data="productForm()" class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <a href="{{ route('products.index') }}" class="hover:text-gray-700">Products</a>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-900">Create New Product</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">Create New Product</h1>
                        <p class="mt-1 text-gray-500">Add a new product with variants to your store</p>
                    </div>
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Products
                    </a>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="flex items-center">
                            <div class="flex items-center gap-2 cursor-pointer" @click="goToStep(index)">
                                <div :class="currentStep >= index 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-200 text-gray-500'"
                                     class="w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all">
                                    <span x-text="index + 1"></span>
                                </div>
                                <span :class="currentStep >= index ? 'text-blue-600 font-medium' : 'text-gray-500'"
                                      class="hidden sm:block" x-text="step.name"></span>
                            </div>
                            <div x-show="index < steps.length - 1" 
                                 :class="currentStep > index ? 'bg-blue-600' : 'bg-gray-200'"
                                 class="w-16 sm:w-24 h-1 mx-4 rounded transition-all"></div>
                        </div>
                    </template>
                </div>
            </div>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-x-4"
                     x-transition:enter-end="opacity-100 transform translate-x-0">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Basic Information
                            </h2>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Product Names -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Product Name (Arabic) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="formData.name_arabic"
                                           name="name_arabic"
                                           dir="rtl"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                           placeholder="اسم المنتج بالعربية"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Enter the product name in Arabic</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Product Name (German) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="formData.name_german"
                                           name="name_german"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                           placeholder="Produktname auf Deutsch"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Enter the product name in German</p>
                                </div>
                            </div>

                            <!-- Descriptions -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description (Arabic)
                                    </label>
                                    <textarea x-model="formData.description_arabic"
                                              name="description_arabic"
                                              dir="rtl"
                                              rows="4"
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                                              placeholder="وصف المنتج بالعربية..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description (German)
                                    </label>
                                    <textarea x-model="formData.description_german"
                                              name="description_german"
                                              rows="4"
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                                              placeholder="Produktbeschreibung auf Deutsch..."></textarea>
                                </div>
                            </div>

                            <!-- Category & Vendor -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="formData.category_id"
                                            name="category_id"
                                            @change="loadSubCategories()"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                            required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name_arabic }} / {{ $category->name_german }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sub Category
                                    </label>
                                    <select x-model="formData.child_category_id"
                                            name="child_category_id"
                                            :disabled="!subCategories.length"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all disabled:bg-gray-100">
                                        <option value="">Select Sub Category</option>
                                        <template x-for="sub in subCategories" :key="sub.id">
                                            <option :value="sub.id" x-text="sub.name_arabic + ' / ' + sub.name_german"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Vendor <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="formData.vendor_id"
                                            name="vendor_id"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                            required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->store_name_in_arabic }} / {{ $vendor->store_name_in_german }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estimated Weight (KG)
                                    </label>
                                    <input type="number" 
                                           x-model="formData.weight"
                                           name="weight"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                           placeholder="0.00">
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           x-model="formData.is_active"
                                           name="is_active"
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Product is Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Product Images -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-x-4"
                     x-transition:enter-end="opacity-100 transform translate-x-0">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-purple-500 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Product Images
                            </h2>
                            <p class="text-purple-100 text-sm mt-1">Upload images and assign them to colors/patterns</p>
                        </div>
                        
                        <div class="p-6">
                            <!-- Info Box -->
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                                <div class="flex gap-3">
                                    <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h4 class="font-medium text-blue-800">Image & Color Association</h4>
                                        <p class="text-sm text-blue-600 mt-1">
                                            Each image can be linked to a specific color. When a customer selects that color, this image will be displayed.
                                            Leave the color field empty for general product images.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Image Button -->
                            <button type="button"
                                    @click="addProductImage()"
                                    class="mb-6 inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors shadow-lg shadow-purple-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Image
                            </button>

                            <!-- Images Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <template x-for="(image, index) in productImages" :key="index">
                                    <div class="bg-gray-50 rounded-xl p-4 border-2 border-dashed border-gray-200 hover:border-purple-300 transition-colors">
                                        <!-- Image Preview -->
                                        <div class="relative aspect-square mb-4 bg-white rounded-lg overflow-hidden border border-gray-200">
                                            <template x-if="image.preview">
                                                <img :src="image.preview" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!image.preview">
                                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="text-sm">No image</span>
                                                </div>
                                            </template>
                                            
                                            <!-- Remove Button -->
                                            <button type="button"
                                                    @click="removeProductImage(index)"
                                                    class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- File Input -->
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Image File</label>
                                            <input type="file"
                                                   :name="'product_images[' + index + '][image]'"
                                                   @change="handleImageUpload($event, index)"
                                                   accept="image/*"
                                                   class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                                        </div>

                                        <!-- Color Assignment -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Linked Color</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color"
                                                       x-model="image.color"
                                                       class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer">
                                                <input type="text"
                                                       x-model="image.color"
                                                       :name="'product_images[' + index + '][color]'"
                                                       placeholder="#000000 or leave empty"
                                                       class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200">
                                                <button type="button"
                                                        @click="image.color = ''"
                                                        class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm">
                                                    Clear
                                                </button>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">Optional: Link this image to a specific color</p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="productImages.length === 0" class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-gray-500 mb-4">No images added yet</p>
                                <button type="button"
                                        @click="addProductImage()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add First Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Product Variants -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-x-4"
                     x-transition:enter-end="opacity-100 transform translate-x-0">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Product Variants
                            </h2>
                            <p class="text-green-100 text-sm mt-1">Add different variations (size, color, pattern, etc.)</p>
                        </div>
                        
                        <div class="p-6">
                            <!-- Variant Explanation -->
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                                <div class="flex gap-3">
                                    <svg class="w-6 h-6 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <h4 class="font-medium text-amber-800">How Variants Work</h4>
                                        <ul class="text-sm text-amber-700 mt-1 space-y-1">
                                            <li>• Each variant represents a unique product combination (e.g., Red + Size M)</li>
                                            <li>• <strong>Color</strong> attribute: Stored as hex color code (e.g., #FF0000)</li>
                                            <li>• <strong>Pattern</strong> attribute: Upload an image that will display instead of solid color</li>
                                            <li>• When pattern is used, the color still determines which product image shows</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Variant Button -->
                            <button type="button"
                                    @click="addVariant()"
                                    class="mb-6 inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors shadow-lg shadow-green-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Variant
                            </button>

                            <!-- Variants List -->
                            <div class="space-y-6">
                                <template x-for="(variant, vIndex) in variants" :key="vIndex">
                                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 overflow-hidden">
                                        <!-- Variant Header -->
                                        <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-semibold text-sm"
                                                      x-text="vIndex + 1"></span>
                                                <span class="font-medium text-gray-700">Variant #<span x-text="vIndex + 1"></span></span>
                                                <template x-if="variant.attributes.length > 0">
                                                    <div class="flex items-center gap-2 ml-4">
                                                        <template x-for="attr in getVariantSummary(variant)" :key="attr.name">
                                                            <span class="px-2 py-1 bg-white text-xs rounded-full border border-gray-200"
                                                                  x-text="attr.display"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <button type="button"
                                                    @click="removeVariant(vIndex)"
                                                    x-show="variants.length > 1"
                                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="p-4 space-y-4">
                                            <!-- Pricing & Stock Row -->
                                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Price (IQD) *</label>
                                                    <input type="number"
                                                           x-model="variant.price"
                                                           :name="'variants[' + vIndex + '][price]'"
                                                           step="0.01"
                                                           min="0"
                                                           required
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm"
                                                           placeholder="0.00">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Cost (IQD)</label>
                                                    <input type="number"
                                                           x-model="variant.cost"
                                                           :name="'variants[' + vIndex + '][cost]'"
                                                           step="0.01"
                                                           min="0"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm"
                                                           placeholder="0.00">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock *</label>
                                                    <input type="number"
                                                           x-model="variant.stock"
                                                           :name="'variants[' + vIndex + '][stock]'"
                                                           min="0"
                                                           required
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm"
                                                           placeholder="0">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tax (%)</label>
                                                    <input type="number"
                                                           x-model="variant.tax"
                                                           :name="'variants[' + vIndex + '][tax]'"
                                                           min="0"
                                                           max="100"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm"
                                                           placeholder="0">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                                    <input type="date"
                                                           x-model="variant.expiry_date"
                                                           :name="'variants[' + vIndex + '][expiry_date]'"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Days</label>
                                                    <input type="number"
                                                           x-model="variant.expiry_days"
                                                           :name="'variants[' + vIndex + '][expiry_days]'"
                                                           min="0"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm"
                                                           placeholder="Days">
                                                </div>
                                            </div>

                                            <!-- Color Tag -->
                                            <div class="max-w-xs">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Color Tag</label>
                                                <select x-model="variant.color_tag_id"
                                                        :name="'variants[' + vIndex + '][color_tag_id]'"
                                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                    <option value="">Select Color Tag</option>
                                                    @foreach($colorTags as $tag)
                                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Attributes Section -->
                                            <div class="border-t border-gray-200 pt-4 mt-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="text-sm font-semibold text-gray-700">Variant Attributes</h4>
                                                    <button type="button"
                                                            @click="addAttribute(vIndex)"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Add Attribute
                                                    </button>
                                                </div>

                                                <div class="space-y-3">
                                                    <template x-for="(attr, aIndex) in variant.attributes" :key="aIndex">
                                                        <div class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200">
                                                            <!-- Attribute Type Selector -->
                                                            <div class="w-48">
                                                                <select x-model="attr.attribute_id"
                                                                        :name="'variants[' + vIndex + '][attributes][' + aIndex + '][attribute_id]'"
                                                                        @change="onAttributeTypeChange(vIndex, aIndex)"
                                                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm"
                                                                        required>
                                                                    <option value="">Select Type</option>
                                                                    @foreach($variantAttributes as $va)
                                                                        <option value="{{ $va->id }}" 
                                                                                data-type="{{ $va->type }}"
                                                                                data-type-values="{{ $va->type_values }}">
                                                                            {{ $va->name_arabic }} / {{ $va->name_german }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <!-- Dynamic Value Input -->
                                                            <div class="flex-1">
                                                                <!-- Color Input -->
                                                                <template x-if="getAttributeType(attr.attribute_id) === 'color'">
                                                                    <div class="flex items-center gap-2">
                                                                        <input type="color"
                                                                               x-model="attr.value"
                                                                               class="w-12 h-10 rounded-lg border border-gray-200 cursor-pointer">
                                                                        <input type="text"
                                                                               x-model="attr.value"
                                                                               :name="'variants[' + vIndex + '][attributes][' + aIndex + '][value]'"
                                                                               placeholder="#000000"
                                                                               class="flex-1 px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm">
                                                                    </div>
                                                                </template>

                                                                <!-- Pattern Input -->
                                                                <template x-if="getAttributeType(attr.attribute_id) === 'pattern'">
                                                                    <div class="space-y-2">
                                                                        <input type="file"
                                                                               :name="'variants[' + vIndex + '][attributes][' + aIndex + '][value]'"
                                                                               @change="handlePatternUpload($event, vIndex, aIndex)"
                                                                               accept="image/*"
                                                                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                                        <template x-if="attr.patternPreview">
                                                                            <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                                                                                <img :src="attr.patternPreview" class="w-full h-full object-cover">
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>

                                                                <!-- Text Input (default) -->
                                                                <template x-if="getAttributeType(attr.attribute_id) === 'text' || !getAttributeType(attr.attribute_id)">
                                                                    <input type="text"
                                                                           x-model="attr.value"
                                                                           :name="'variants[' + vIndex + '][attributes][' + aIndex + '][value]'"
                                                                           placeholder="Enter value (e.g., M, L, XL)"
                                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm">
                                                                </template>

                                                                <!-- Select Input -->
                                                                <template x-if="getAttributeType(attr.attribute_id) === 'select'">
                                                                    <select x-model="attr.value"
                                                                            :name="'variants[' + vIndex + '][attributes][' + aIndex + '][value]'"
                                                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm">
                                                                        <option value="">Select Value</option>
                                                                        <template x-for="opt in getAttributeOptions(attr.attribute_id)" :key="opt">
                                                                            <option :value="opt" x-text="opt"></option>
                                                                        </template>
                                                                    </select>
                                                                </template>
                                                            </div>

                                                            <!-- Remove Attribute -->
                                                            <button type="button"
                                                                    @click="removeAttribute(vIndex, aIndex)"
                                                                    x-show="variant.attributes.length > 1"
                                                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="variants.length === 0" class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <p class="text-gray-500 mb-4">No variants added yet</p>
                                <button type="button"
                                        @click="addVariant()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add First Variant
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Submit -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-x-4"
                     x-transition:enter-end="opacity-100 transform translate-x-0">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Review & Submit
                            </h2>
                            <p class="text-indigo-100 text-sm mt-1">Review your product information before submitting</p>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Product Summary -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Info Summary -->
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Basic Information
                                    </h3>
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Name (AR):</dt>
                                            <dd class="font-medium text-gray-900" x-text="formData.name_arabic || '-'"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Name (DE):</dt>
                                            <dd class="font-medium text-gray-900" x-text="formData.name_german || '-'"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Category:</dt>
                                            <dd class="font-medium text-gray-900" x-text="getCategoryName() || '-'"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Vendor:</dt>
                                            <dd class="font-medium text-gray-900" x-text="getVendorName() || '-'"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Status:</dt>
                                            <dd>
                                                <span :class="formData.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                      class="px-2 py-1 rounded-full text-xs font-medium"
                                                      x-text="formData.is_active ? 'Active' : 'Inactive'"></span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Images Summary -->
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Product Images
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(img, i) in productImages" :key="i">
                                            <div class="w-16 h-16 rounded-lg overflow-hidden border-2 border-white shadow-sm">
                                                <img :src="img.preview" class="w-full h-full object-cover">
                                            </div>
                                        </template>
                                        <div x-show="productImages.length === 0" class="text-gray-500 text-sm">
                                            No images added
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        <span x-text="productImages.length"></span> image(s) uploaded
                                    </p>
                                </div>
                            </div>

                            <!-- Variants Summary -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    Variants (<span x-text="variants.length"></span>)
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-500 border-b border-gray-200">
                                                <th class="pb-2 font-medium">#</th>
                                                <th class="pb-2 font-medium">Attributes</th>
                                                <th class="pb-2 font-medium">Price</th>
                                                <th class="pb-2 font-medium">Stock</th>
                                                <th class="pb-2 font-medium">Tax</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(v, i) in variants" :key="i">
                                                <tr>
                                                    <td class="py-2" x-text="i + 1"></td>
                                                    <td class="py-2">
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="attr in getVariantSummary(v)" :key="attr.name">
                                                                <span class="px-2 py-0.5 bg-white text-xs rounded border border-gray-200"
                                                                      x-text="attr.display"></span>
                                                            </template>
                                                        </div>
                                                    </td>
                                                    <td class="py-2 font-medium">IQD<span x-text="v.price || '0'"></span></td>
                                                    <td class="py-2" x-text="v.stock || '0'"></td>
                                                    <td class="py-2"><span x-text="v.tax || '0'"></span>%</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Validation Warnings -->
                            <div x-show="validationErrors.length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
                                <h4 class="font-medium text-red-800 flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Please fix the following errors:
                                </h4>
                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                    <template x-for="error in validationErrors" :key="error">
                                        <li x-text="error"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex items-center justify-between">
                    <button type="button"
                            x-show="currentStep > 0"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </button>
                    <div x-show="currentStep === 0"></div>

                    <div class="flex items-center gap-4">
                        <button type="button"
                                x-show="currentStep < steps.length - 1"
                                @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">
                            Next
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <button type="submit"
                                x-show="currentStep === steps.length - 1"
                                :disabled="isSubmitting || validationErrors.length > 0"
                                class="inline-flex items-center gap-2 px-8 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors shadow-lg shadow-green-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!isSubmitting">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="isSubmitting">
                                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isSubmitting ? 'Creating...' : 'Create Product'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function productForm() {
            return {
                // Steps configuration
                steps: [
                    { name: 'Basic Info' },
                    { name: 'Images' },
                    { name: 'Variants' },
                    { name: 'Review' }
                ],
                currentStep: 0,
                isSubmitting: false,
                validationErrors: [],

                // Form data
                formData: {
                    name_arabic: '',
                    name_german: '',
                    description_arabic: '',
                    description_german: '',
                    category_id: '',
                    child_category_id: '',
                    vendor_id: '',
                    weight: 0,
                    is_active: true
                },

                // Categories data
                categories: @json($categories),
                subCategories: [],

                // Vendors data
                vendors: @json($vendors),

                // Variant attributes data
                variantAttributes: @json($variantAttributes),

                // Product images
                productImages: [],

                // Product variants
                variants: [],

                // Initialize
                init() {
                    // Add one default variant
                    this.addVariant();
                },

                // Navigation
                goToStep(step) {
                    if (step <= this.currentStep) {
                        this.currentStep = step;
                    }
                },

                nextStep() {
                    if (this.validateCurrentStep()) {
                        this.currentStep++;
                        if (this.currentStep === 3) {
                            this.validateAll();
                        }
                    }
                },

                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                    }
                },

                // Validation
                validateCurrentStep() {
                    switch (this.currentStep) {
                        case 0:
                            return this.validateBasicInfo();
                        case 1:
                            return this.validateImages();
                        case 2:
                            return this.validateVariants();
                        default:
                            return true;
                    }
                },

                validateBasicInfo() {
                    if (!this.formData.name_arabic.trim()) {
                        alert('Please enter the product name in Arabic');
                        return false;
                    }
                    if (!this.formData.name_german.trim()) {
                        alert('Please enter the product name in German');
                        return false;
                    }
                    if (!this.formData.category_id) {
                        alert('Please select a category');
                        return false;
                    }
                    if (!this.formData.vendor_id) {
                        alert('Please select a vendor');
                        return false;
                    }
                    return true;
                },

                validateImages() {
                    if (this.productImages.length === 0) {
                        alert('Please add at least one product image');
                        return false;
                    }
                    for (let img of this.productImages) {
                        if (!img.file) {
                            alert('Please select an image file for all image slots');
                            return false;
                        }
                    }
                    return true;
                },

                validateVariants() {
                    if (this.variants.length === 0) {
                        alert('Please add at least one variant');
                        return false;
                    }
                    for (let i = 0; i < this.variants.length; i++) {
                        const v = this.variants[i];
                        if (!v.price || v.price <= 0) {
                            alert(`Variant ${i + 1}: Please enter a valid price`);
                            return false;
                        }
                        if (v.stock === '' || v.stock < 0) {
                            alert(`Variant ${i + 1}: Please enter a valid stock quantity`);
                            return false;
                        }
                        if (v.attributes.length === 0) {
                            alert(`Variant ${i + 1}: Please add at least one attribute`);
                            return false;
                        }
                        for (let j = 0; j < v.attributes.length; j++) {
                            const attr = v.attributes[j];
                            if (!attr.attribute_id) {
                                alert(`Variant ${i + 1}: Please select an attribute type`);
                                return false;
                            }
                            const attrType = this.getAttributeType(attr.attribute_id);
                            if (attrType !== 'pattern' && !attr.value) {
                                alert(`Variant ${i + 1}: Please enter a value for all attributes`);
                                return false;
                            }
                        }
                    }
                    return true;
                },

                validateAll() {
                    this.validationErrors = [];
                    
                    if (!this.formData.name_arabic.trim()) {
                        this.validationErrors.push('Product name in Arabic is required');
                    }
                    if (!this.formData.name_german.trim()) {
                        this.validationErrors.push('Product name in German is required');
                    }
                    if (!this.formData.category_id) {
                        this.validationErrors.push('Category is required');
                    }
                    if (!this.formData.vendor_id) {
                        this.validationErrors.push('Vendor is required');
                    }
                    if (this.productImages.length === 0) {
                        this.validationErrors.push('At least one product image is required');
                    }
                    if (this.variants.length === 0) {
                        this.validationErrors.push('At least one variant is required');
                    }
                },

                // Category handling
                loadSubCategories() {
                    const category = this.categories.find(c => c.id == this.formData.category_id);
                    this.subCategories = category?.children || [];
                    this.formData.child_category_id = '';
                },

                // Image handling
                addProductImage() {
                    this.productImages.push({
                        file: null,
                        preview: null,
                        color: ''
                    });
                },

                removeProductImage(index) {
                    this.productImages.splice(index, 1);
                },

                handleImageUpload(event, index) {
                    const file = event.target.files[0];
                    if (file) {
                        this.productImages[index].file = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.productImages[index].preview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                // Variant handling
                addVariant() {
                    this.variants.push({
                        price: '',
                        cost: '',
                        stock: '',
                        tax: '',
                        expiry_date: '',
                        expiry_days: '',
                        color_tag_id: '',
                        attributes: [
                            { attribute_id: '', value: '', patternPreview: null }
                        ]
                    });
                },

                removeVariant(index) {
                    this.variants.splice(index, 1);
                },

                addAttribute(variantIndex) {
                    this.variants[variantIndex].attributes.push({
                        attribute_id: '',
                        value: '',
                        patternPreview: null
                    });
                },

                removeAttribute(variantIndex, attrIndex) {
                    this.variants[variantIndex].attributes.splice(attrIndex, 1);
                },

                onAttributeTypeChange(variantIndex, attrIndex) {
                    this.variants[variantIndex].attributes[attrIndex].value = '';
                    this.variants[variantIndex].attributes[attrIndex].patternPreview = null;
                },

                getAttributeType(attributeId) {
                    if (!attributeId) return null;
                    const attr = this.variantAttributes.find(a => a.id == attributeId);
                    return attr?.type || 'text';
                },

                getAttributeOptions(attributeId) {
                    if (!attributeId) return [];
                    const attr = this.variantAttributes.find(a => a.id == attributeId);
                    if (attr?.type_values) {
                        return attr.type_values.split(',').map(v => v.trim());
                    }
                    return [];
                },

                handlePatternUpload(event, variantIndex, attrIndex) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.variants[variantIndex].attributes[attrIndex].patternPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                getVariantSummary(variant) {
                    return variant.attributes
                        .filter(a => a.attribute_id && a.value)
                        .map(a => {
                            const attr = this.variantAttributes.find(va => va.id == a.attribute_id);
                            let displayValue = a.value;
                            
                            if (attr?.type === 'color') {
                                displayValue = a.value;
                            } else if (attr?.type === 'pattern') {
                                displayValue = 'Pattern';
                            }
                            
                            return {
                                name: attr?.name_german || 'Unknown',
                                display: `${attr?.name_german || ''}: ${displayValue}`
                            };
                        });
                },

                // Helper functions
                getCategoryName() {
                    const cat = this.categories.find(c => c.id == this.formData.category_id);
                    return cat ? `${cat.name_arabic} / ${cat.name_german}` : '';
                },

                getVendorName() {
                    const vendor = this.vendors.find(v => v.id == this.formData.vendor_id);
                    return vendor ? `${vendor.store_name_in_arabic} / ${vendor.store_name_in_german}` : '';
                },

                // Form submission
                async submitForm() {
                    this.validateAll();
                    if (this.validationErrors.length > 0) {
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const formData = new FormData();
                        
                        // Add basic data
                        formData.append('name_arabic', this.formData.name_arabic);
                        formData.append('name_german', this.formData.name_german);
                        formData.append('description_arabic', this.formData.description_arabic);
                        formData.append('description_german', this.formData.description_german);
                        formData.append('category_id', this.formData.category_id);
                        if (this.formData.child_category_id) {
                            formData.append('child_category_id', this.formData.child_category_id);
                        }
                        formData.append('vendor_id', this.formData.vendor_id);
                        formData.append('weight', this.formData.weight);
                        formData.append('is_active', this.formData.is_active ? '1' : '0');

                        // Add images
                        this.productImages.forEach((img, i) => {
                            if (img.file) {
                                formData.append(`product_images[${i}][image]`, img.file);
                                formData.append(`product_images[${i}][color]`, this.convertColorToFlutter(img.color));
                            }
                        });

                        // Add variants
                        this.variants.forEach((v, vi) => {
                            formData.append(`variants[${vi}][price]`, v.price);
                            formData.append(`variants[${vi}][cost]`, v.cost || 0);
                            formData.append(`variants[${vi}][stock]`, v.stock);
                            formData.append(`variants[${vi}][tax]`, v.tax || 0);
                            if (v.expiry_date) formData.append(`variants[${vi}][expiry_date]`, v.expiry_date);
                            if (v.expiry_days) formData.append(`variants[${vi}][expiry_days]`, v.expiry_days);
                            if (v.color_tag_id) formData.append(`variants[${vi}][color_tag_id]`, v.color_tag_id);

                            // Add attributes
                            v.attributes.forEach((attr, ai) => {
                                formData.append(`variants[${vi}][attributes][${ai}][attribute_id]`, attr.attribute_id);
                                
                                const attrType = this.getAttributeType(attr.attribute_id);
                                if (attrType === 'pattern') {
                                    // For pattern, we need to get the file from the input
                                    const fileInput = document.querySelector(`input[name="variants[${vi}][attributes][${ai}][value]"]`);
                                    if (fileInput && fileInput.files[0]) {
                                        formData.append(`variants[${vi}][attributes][${ai}][value]`, fileInput.files[0]);
                                    }
                                } else if (attrType === 'color') {
                                    formData.append(`variants[${vi}][attributes][${ai}][value]`, this.convertColorToFlutter(attr.value));
                                } else {
                                    formData.append(`variants[${vi}][attributes][${ai}][value]`, attr.value);
                                }
                            });
                        });

                        const response = await fetch('{{ route("products.store") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Show success message
                            alert('Product created successfully!');
                            // Redirect
                            window.location.href = result.redirect || '{{ route("products.index") }}';
                        } else {
                            // Show errors
                            if (result.errors) {
                                const errorMessages = Object.values(result.errors).flat();
                                alert('Validation errors:\n' + errorMessages.join('\n'));
                            } else {
                                alert(result.message || 'Failed to create product');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                convertColorToFlutter(hexColor) {
                    if (!hexColor) return '';
                    const hex = hexColor.replace('#', '');
                    if (hex.length === 6) {
                        return '0xFF' + hex.toUpperCase();
                    }
                    return hexColor;
                }
            };
        }
    </script>
</x-admin-layout>