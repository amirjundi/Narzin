<x-admin-layout>
    <div x-data="productEditForm()" class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                            <a href="{{ route('products.index') }}" class="hover:text-gray-700">Products</a>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-900">Edit: {{ $product->name_german }}</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
                        <p class="mt-1 text-gray-500">Update product details, images, and variants</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.show', $product->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View Product
                        </a>
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="flex items-center">
                            <button @click="currentStep = index + 1"
                                    :class="currentStep === index + 1 
                                        ? 'bg-gradient-to-r ' + step.gradient + ' text-white shadow-lg' 
                                        : currentStep > index + 1 
                                            ? 'bg-green-500 text-white' 
                                            : 'bg-gray-200 text-gray-600'"
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all duration-300">
                                <template x-if="currentStep > index + 1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="currentStep <= index + 1">
                                    <span x-text="index + 1"></span>
                                </template>
                            </button>
                            <span :class="currentStep === index + 1 ? 'text-gray-900 font-medium' : 'text-gray-500'"
                                  class="ml-2 text-sm hidden sm:block" x-text="step.title"></span>
                            <template x-if="index < steps.length - 1">
                                <div class="w-12 sm:w-24 h-1 mx-2 sm:mx-4 rounded-full"
                                     :class="currentStep > index + 1 ? 'bg-green-500' : 'bg-gray-200'"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Form Container -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                
                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Basic Information
                        </h2>
                        <p class="text-blue-100 mt-1 text-sm">Update product name, description, and categorization</p>
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
                                       dir="rtl"
                                       placeholder="اسم المنتج بالعربية"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-right">
                                <p x-show="errors.name_arabic" class="mt-1 text-sm text-red-500" x-text="errors.name_arabic"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Product Name (German) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="formData.name_german"
                                       placeholder="Produktname auf Deutsch"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                <p x-show="errors.name_german" class="mt-1 text-sm text-red-500" x-text="errors.name_german"></p>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Description (Arabic)
                                </label>
                                <textarea x-model="formData.description_arabic"
                                          dir="rtl"
                                          rows="4"
                                          placeholder="وصف المنتج بالعربية"
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none text-right"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Description (German)
                                </label>
                                <textarea x-model="formData.description_german"
                                          rows="4"
                                          placeholder="Produktbeschreibung auf Deutsch"
                                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                            </div>
                        </div>

                        <!-- Category, Sub Category, Vendor -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select x-model="formData.category_id"
                                        @change="loadSubCategories()"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name_arabic }} / {{ $category->name_german }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.category_id" class="mt-1 text-sm text-red-500" x-text="errors.category_id"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sub Category
                                </label>
                                <select x-model="formData.child_category_id"
                                        :disabled="subCategories.length === 0"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed">
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
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->store_name_in_arabic }} / {{ $vendor->store_name_in_german }}</option>
                                    @endforeach
                                </select>
                                <p x-show="errors.vendor_id" class="mt-1 text-sm text-red-500" x-text="errors.vendor_id"></p>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       x-model="formData.is_active"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Product Status</span>
                                <p class="text-xs text-gray-500" x-text="formData.is_active ? 'Product is visible to customers' : 'Product is hidden from customers'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Product Images -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            Product Images
                        </h2>
                        <p class="text-purple-100 mt-1 text-sm">Manage existing images and add new ones</p>
                    </div>
                    
                    <div class="p-6 space-y-8">
                        <!-- Info Box -->
                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-purple-700">
                                    <p class="font-medium">Image-Color Association</p>
                                    <p class="mt-1">Link images to specific colors. When customers select a color variant, the matching image will display.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Images -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span>Current Images</span>
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-sm" x-text="existingImages.length"></span>
                            </h3>
                            
                            <div x-show="existingImages.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                <template x-for="(image, index) in existingImages" :key="image.id">
                                    <div class="relative group">
                                        <div class="aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                             :class="image.markedForDelete ? 'border-red-500 opacity-50' : 'border-gray-200 group-hover:border-purple-300'">
                                            <img :src="image.url" 
                                                 alt="Product Image"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        
                                        <!-- Color Badge -->
                                        <template x-if="image.color">
                                            <div class="absolute bottom-2 left-2 flex items-center gap-1 px-2 py-1 bg-white/95 backdrop-blur rounded-lg shadow-sm border border-gray-100">
                                                <div class="w-4 h-4 rounded-full border border-gray-300" 
                                                     :style="'background-color: ' + image.displayColor"></div>
                                                <span class="text-xs font-mono text-gray-600" x-text="image.color"></span>
                                            </div>
                                        </template>
                                        
                                        <!-- Delete/Restore Button -->
                                        <button type="button"
                                                @click="toggleDeleteExistingImage(index)"
                                                :class="image.markedForDelete 
                                                    ? 'bg-green-500 hover:bg-green-600' 
                                                    : 'bg-red-500 hover:bg-red-600 opacity-0 group-hover:opacity-100'"
                                                class="absolute top-2 right-2 w-8 h-8 text-white rounded-full flex items-center justify-center transition-all shadow-lg">
                                            <svg x-show="!image.markedForDelete" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <svg x-show="image.markedForDelete" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                        </button>
                                        
                                        <!-- Delete Overlay -->
                                        <div x-show="image.markedForDelete" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <span class="px-3 py-1 bg-red-500 text-white text-xs font-medium rounded-full">Will be deleted</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <div x-show="existingImages.length === 0" class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-gray-500">No images uploaded yet</p>
                            </div>
                        </div>

                        <!-- Add New Images -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Add New Images</h3>
                                <button type="button"
                                        @click="addNewImage()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Image Slot
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <template x-for="(image, index) in newImages" :key="index">
                                    <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-4 border-2 border-dashed border-purple-200">
                                        <!-- Preview -->
                                        <div class="relative aspect-square mb-3 bg-white rounded-lg overflow-hidden border border-gray-200">
                                            <template x-if="image.preview">
                                                <img :src="image.preview" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!image.preview">
                                                <label :for="'new-image-' + index" class="w-full h-full flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:text-purple-500 transition-colors">
                                                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                    </svg>
                                                    <span class="text-sm">Click to upload</span>
                                                </label>
                                            </template>
                                            
                                            <!-- Remove Button -->
                                            <button type="button"
                                                    @click="removeNewImage(index)"
                                                    class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- File Input -->
                                        <input type="file"
                                               :id="'new-image-' + index"
                                               @change="handleNewImageUpload($event, index)"
                                               accept="image/*"
                                               class="hidden">
                                        
                                        <template x-if="image.preview">
                                            <label :for="'new-image-' + index" 
                                                   class="block w-full text-center text-xs text-purple-600 hover:text-purple-700 cursor-pointer mb-2">
                                                Change image
                                            </label>
                                        </template>
                                        
                                        <!-- Color Selection -->
                                        <div class="flex items-center gap-2">
                                            <input type="color"
                                                   x-model="image.color"
                                                   class="w-10 h-8 rounded border border-gray-200 cursor-pointer">
                                            <input type="text"
                                                   x-model="image.color"
                                                   placeholder="#RRGGBB"
                                                   class="flex-1 px-2 py-1.5 rounded-lg border border-gray-200 text-sm font-mono focus:border-purple-500 focus:ring-1 focus:ring-purple-200">
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Empty State / Add Button -->
                                <template x-if="newImages.length === 0">
                                    <button type="button"
                                            @click="addNewImage()"
                                            class="aspect-square rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 hover:border-purple-400 hover:text-purple-500 transition-colors">
                                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="text-sm font-medium">Add Image</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Product Variants -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-r from-green-600 to-green-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            Product Variants
                        </h2>
                        <p class="text-green-100 mt-1 text-sm">Manage pricing, stock, and variant attributes</p>
                    </div>
                    
                    <div class="p-6 space-y-8">
                        <!-- Info Box -->
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-green-700">
                                    <p class="font-medium">Variant Attributes</p>
                                    <p class="mt-1"><strong>Color:</strong> Hex color code (stored as 0xFFRRGGBB for Flutter)</p>
                                    <p><strong>Pattern:</strong> Image that replaces color swatch display</p>
                                    <p><strong>Text:</strong> Free text like size (S, M, L, XL)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Variants -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span>Current Variants</span>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-sm" x-text="existingVariants.length"></span>
                            </h3>
                            
                            <div class="space-y-4">
                                <template x-for="(variant, vIndex) in existingVariants" :key="variant.id">
                                    <div class="rounded-xl border-2 overflow-hidden transition-all"
                                         :class="variant.markedForDelete ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'">
                                        
                                        <!-- Variant Header -->
                                        <div class="px-4 py-3 flex items-center justify-between cursor-pointer"
                                             :class="variant.markedForDelete ? 'bg-red-100' : 'bg-gray-50'"
                                             @click="variant.expanded = !variant.expanded">
                                            <div class="flex items-center gap-3">
                                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold text-white"
                                                      :class="variant.markedForDelete ? 'bg-red-500' : 'bg-green-600'">
                                                    <span x-text="vIndex + 1"></span>
                                                </span>
                                                <div>
                                                    <p class="font-medium text-gray-900" x-text="'SKU: ' + variant.sku"></p>
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        <template x-for="attr in variant.attributes" :key="attr.id">
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white border border-gray-200 rounded text-xs">
                                                                <span class="text-gray-500" x-text="attr.attribute_name + ':'"></span>
                                                                <template x-if="attr.type === 'color'">
                                                                    <span class="inline-flex items-center gap-1">
                                                                        <span class="w-3 h-3 rounded-full border border-gray-300" :style="'background-color: ' + attr.displayColor"></span>
                                                                    </span>
                                                                </template>
                                                                <template x-if="attr.type === 'pattern'">
                                                                    <span class="text-purple-600 font-medium">Pattern</span>
                                                                </template>
                                                                <template x-if="attr.type !== 'color' && attr.type !== 'pattern'">
                                                                    <span class="font-medium" x-text="attr.value"></span>
                                                                </template>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="text-right">
                                                    <p class="text-lg font-bold text-gray-900">IQD<span x-text="parseFloat(variant.price).toFixed(2)"></span></p>
                                                    <p class="text-sm" :class="variant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                                                        <span x-text="variant.stock"></span> in stock
                                                    </p>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 transition-transform"
                                                     :class="variant.expanded ? 'rotate-180' : ''"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Expanded Details -->
                                        <div x-show="variant.expanded" x-collapse class="border-t"
                                             :class="variant.markedForDelete ? 'border-red-200' : 'border-gray-200'">
                                            <div class="p-4 space-y-4">
                                                <!-- Pricing Grid -->
                                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Price (IQD) *</label>
                                                        <input type="number"
                                                               x-model="variant.price"
                                                               step="0.01"
                                                               min="0"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Cost (IQD)</label>
                                                        <input type="number"
                                                               x-model="variant.cost"
                                                               step="0.01"
                                                               min="0"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Stock *</label>
                                                        <input type="number"
                                                               x-model="variant.stock"
                                                               min="0"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Tax (%)</label>
                                                        <input type="number"
                                                               x-model="variant.tax"
                                                               min="0"
                                                               max="100"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                                        <input type="date"
                                                               x-model="variant.expiry_date"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Days</label>
                                                        <input type="number"
                                                               x-model="variant.expiry_days"
                                                               min="0"
                                                               :disabled="variant.markedForDelete"
                                                               class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm disabled:bg-gray-100">
                                                    </div>
                                                </div>

                                                <!-- Profit Display -->
                                                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <span class="text-xs text-gray-500">Profit:</span>
                                                        <span class="ml-1 font-bold" 
                                                              :class="(variant.price - (variant.cost || 0)) >= 0 ? 'text-green-600' : 'text-red-600'"
                                                              x-text="'IQD' + (variant.price - (variant.cost || 0)).toFixed(2)"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Margin:</span>
                                                        <span class="ml-1 font-bold text-purple-600"
                                                              x-text="variant.cost > 0 ? (((variant.price - variant.cost) / variant.cost) * 100).toFixed(1) + '%' : 'N/A'"></span>
                                                    </div>
                                                </div>

                                                <!-- Status & Delete -->
                                                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" 
                                                                   x-model="variant.is_active"
                                                                   :disabled="variant.markedForDelete"
                                                                   class="sr-only peer">
                                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600 peer-disabled:opacity-50"></div>
                                                        </label>
                                                        <span class="text-sm text-gray-600">Variant Active</span>
                                                    </div>
                                                    
                                                    <button type="button"
                                                            @click="toggleDeleteExistingVariant(vIndex)"
                                                            :class="variant.markedForDelete 
                                                                ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                                                                : 'bg-red-100 text-red-700 hover:bg-red-200'"
                                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                                        <template x-if="!variant.markedForDelete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="variant.markedForDelete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                            </svg>
                                                        </template>
                                                        <span x-text="variant.markedForDelete ? 'Restore Variant' : 'Delete Variant'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Add New Variants -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Add New Variants</h3>
                                <button type="button"
                                        @click="addNewVariant()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Variant
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(variant, vIndex) in newVariants" :key="vIndex">
                                    <div class="bg-gradient-to-br from-green-50 to-white rounded-xl border-2 border-green-200 overflow-hidden">
                                        <!-- Header -->
                                        <div class="bg-green-100 px-4 py-3 flex items-center justify-between">
                                            <span class="font-semibold text-green-800 flex items-center gap-2">
                                                <span class="w-6 h-6 bg-green-600 text-white rounded flex items-center justify-center text-xs" x-text="vIndex + 1"></span>
                                                New Variant
                                            </span>
                                            <button type="button"
                                                    @click="removeNewVariant(vIndex)"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <div class="p-4 space-y-4">
                                            <!-- Pricing Grid -->
                                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Price (IQD) *</label>
                                                    <input type="number"
                                                           x-model="variant.price"
                                                           step="0.01"
                                                           min="0"
                                                           required
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Cost (IQD)</label>
                                                    <input type="number"
                                                           x-model="variant.cost"
                                                           step="0.01"
                                                           min="0"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock *</label>
                                                    <input type="number"
                                                           x-model="variant.stock"
                                                           min="0"
                                                           required
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tax (%)</label>
                                                    <input type="number"
                                                           x-model="variant.tax"
                                                           min="0"
                                                           max="100"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                                    <input type="date"
                                                           x-model="variant.expiry_date"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Days</label>
                                                    <input type="number"
                                                           x-model="variant.expiry_days"
                                                           min="0"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm">
                                                </div>
                                            </div>

                                            <!-- Attributes Section -->
                                            <div class="border-t border-gray-200 pt-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <label class="text-sm font-medium text-gray-700">Variant Attributes</label>
                                                    <button type="button"
                                                            @click="addNewAttribute(vIndex)"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Add Attribute
                                                    </button>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <template x-for="(attr, aIndex) in variant.attributes" :key="aIndex">
                                                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                                                            <!-- Attribute Type Select -->
                                                            <select x-model="attr.attribute_id"
                                                                    @change="onAttributeTypeChange(vIndex, aIndex)"
                                                                    class="w-40 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                                <option value="">Select Type</option>
                                                                <template x-for="va in variantAttributes" :key="va.id">
                                                                    <option :value="va.id" x-text="va.name_german + ' (' + va.type + ')'"></option>
                                                                </template>
                                                            </select>
                                                            
                                                            <!-- Color Input -->
                                                            <template x-if="getAttributeType(attr.attribute_id) === 'color'">
                                                                <div class="flex-1 flex items-center gap-2">
                                                                    <input type="color"
                                                                           x-model="attr.colorPicker"
                                                                           @input="attr.value = $event.target.value"
                                                                           class="w-10 h-9 rounded border border-gray-200 cursor-pointer">
                                                                    <input type="text"
                                                                           x-model="attr.value"
                                                                           @input="attr.colorPicker = $event.target.value"
                                                                           placeholder="#RRGGBB"
                                                                           class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                                </div>
                                                            </template>
                                                            
                                                            <!-- Pattern Input -->
                                                            <template x-if="getAttributeType(attr.attribute_id) === 'pattern'">
                                                                <div class="flex-1 flex items-center gap-2">
                                                                    <template x-if="attr.patternPreview">
                                                                        <img :src="attr.patternPreview" class="w-10 h-10 rounded object-cover border border-gray-200">
                                                                    </template>
                                                                    <input type="file"
                                                                           @change="handlePatternUpload($event, vIndex, aIndex)"
                                                                           accept="image/*"
                                                                           class="flex-1 text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                                                </div>
                                                            </template>
                                                            
                                                            <!-- Text/Select Input -->
                                                            <template x-if="getAttributeType(attr.attribute_id) !== 'color' && getAttributeType(attr.attribute_id) !== 'pattern'">
                                                                <input type="text"
                                                                       x-model="attr.value"
                                                                       placeholder="Enter value (e.g., M, L, XL)"
                                                                       class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                            </template>
                                                            
                                                            <!-- Remove Attribute -->
                                                            <button type="button"
                                                                    @click="removeNewAttribute(vIndex, aIndex)"
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
                                
                                <!-- Empty State -->
                                <template x-if="newVariants.length === 0">
                                    <div class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <p class="text-gray-500 mb-3">No new variants added</p>
                                        <button type="button"
                                                @click="addNewVariant()"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add First Variant
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Save -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Review Changes
                        </h2>
                        <p class="text-indigo-100 mt-1 text-sm">Review all changes before saving</p>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Changes Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Images to Delete -->
                            <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span class="text-sm font-medium text-red-700">Images to Delete</span>
                                </div>
                                <p class="text-2xl font-bold text-red-600" x-text="existingImages.filter(i => i.markedForDelete).length"></p>
                            </div>
                            
                            <!-- New Images -->
                            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-green-700">New Images</span>
                                </div>
                                <p class="text-2xl font-bold text-green-600" x-text="newImages.filter(i => i.file).length"></p>
                            </div>
                            
                            <!-- Variants to Delete -->
                            <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span class="text-sm font-medium text-red-700">Variants to Delete</span>
                                </div>
                                <p class="text-2xl font-bold text-red-600" x-text="existingVariants.filter(v => v.markedForDelete).length"></p>
                            </div>
                            
                            <!-- New Variants -->
                            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-green-700">New Variants</span>
                                </div>
                                <p class="text-2xl font-bold text-green-600" x-text="newVariants.length"></p>
                            </div>
                        </div>

                        <!-- Basic Info Summary -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-900">Basic Information</h3>
                            </div>
                            <div class="p-4">
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-xs text-gray-500">Name (Arabic)</dt>
                                        <dd class="font-medium text-gray-900" dir="rtl" x-text="formData.name_arabic || '-'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-500">Name (German)</dt>
                                        <dd class="font-medium text-gray-900" x-text="formData.name_german || '-'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-500">Category</dt>
                                        <dd class="font-medium text-gray-900" x-text="getCategoryName() || '-'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-500">Vendor</dt>
                                        <dd class="font-medium text-gray-900" x-text="getVendorName() || '-'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-gray-500">Status</dt>
                                        <dd>
                                            <span :class="formData.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                                  class="px-2 py-0.5 rounded text-sm font-medium"
                                                  x-text="formData.is_active ? 'Active' : 'Inactive'"></span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Validation Errors -->
                        <div x-show="Object.keys(errors).length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-red-700">Please fix the following errors:</h4>
                                    <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                        <template x-for="(error, field) in errors" :key="field">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <button type="button"
                            @click="prevStep()"
                            x-show="currentStep > 1"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </button>
                    
                    <div x-show="currentStep === 1"></div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.show', $product->id) }}" 
                           class="px-5 py-2.5 text-gray-600 hover:text-gray-800 transition-colors">
                            Cancel
                        </a>
                        
                        <button type="button"
                                @click="nextStep()"
                                x-show="currentStep < 4"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            Next
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <button type="button"
                                @click="submitForm()"
                                x-show="currentStep === 4"
                                :disabled="isSubmitting || Object.keys(errors).length > 0"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!isSubmitting">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="isSubmitting">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isSubmitting ? 'Saving...' : 'Save All Changes'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productEditForm() {
            return {
                currentStep: 1,
                isSubmitting: false,
                errors: {},

                steps: [
                    { title: 'Basic Info', gradient: 'from-blue-600 to-blue-500' },
                    { title: 'Images', gradient: 'from-purple-600 to-purple-500' },
                    { title: 'Variants', gradient: 'from-green-600 to-green-500' },
                    { title: 'Review', gradient: 'from-indigo-600 to-indigo-500' }
                ],

                // Form Data
                formData: {
                    name_arabic: @json($product->name_arabic ?? ''),
                    name_german: @json($product->name_german ?? ''),
                    description_arabic: @json($product->description_arabic ?? ''),
                    description_german: @json($product->description_german ?? ''),
                    category_id: @json($product->category_id ?? ''),
                    child_category_id: @json($product->child_category_id ?? ''),
                    vendor_id: @json($product->vendor_id ?? ''),
                    is_active: @json($product->is_active ?? true)
                },

                // Categories & Vendors
                categories: @json($categories),
                vendors: @json($vendors),
                subCategories: [],
                variantAttributes: @json($variantAttributes),

                // Existing Images
                existingImages: @json($product->images->map(function($img) {
                    $hexColor = str_replace(['0xFF', '0x'], '#', $img->color ?? '');
                    return [
                        'id' => $img->id,
                        'url' => Storage::url($img->image),
                        'color' => $img->color,
                        'displayColor' => $hexColor ?: '#cccccc',
                        'markedForDelete' => false
                    ];
                })),

                // New Images
                newImages: [],

                // Existing Variants
                existingVariants: @json($product->variants->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'cost' => $variant->cost,
                        'stock' => $variant->stock,
                        'tax' => $variant->tax,
                        'expiry_date' => $variant->expiry_date,
                        'expiry_days' => $variant->expiry_days,
                        'is_active' => $variant->is_active,
                        'markedForDelete' => false,
                        'expanded' => false,
                        'attributes' => $variant->variantValues->map(function($val) {
                            $hexColor = str_replace(['0xFF', '0x'], '#', $val->value);
                            return [
                                'id' => $val->id,
                                'attribute_id' => $val->variant_attribute_id,
                                'attribute_name' => $val->variantAttribute->name_german ?? '',
                                'type' => $val->variantAttribute->type ?? 'text',
                                'value' => $val->value,
                                'displayColor' => $hexColor ?: '#cccccc'
                            ];
                        })
                    ];
                })),

                // New Variants
                newVariants: [],

                init() {
                    this.loadSubCategories();
                },

                // Navigation
                nextStep() {
                    if (this.validateCurrentStep()) {
                        this.currentStep++;
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },

                validateCurrentStep() {
                    this.errors = {};

                    if (this.currentStep === 1) {
                        if (!this.formData.name_arabic) this.errors.name_arabic = 'Arabic name is required';
                        if (!this.formData.name_german) this.errors.name_german = 'German name is required';
                        if (!this.formData.category_id) this.errors.category_id = 'Category is required';
                        if (!this.formData.vendor_id) this.errors.vendor_id = 'Vendor is required';
                    }

                    return Object.keys(this.errors).length === 0;
                },

                // Category Management
                loadSubCategories() {
                    const category = this.categories.find(c => c.id == this.formData.category_id);
                    this.subCategories = category?.children || [];
                    if (!this.subCategories.find(s => s.id == this.formData.child_category_id)) {
                        this.formData.child_category_id = '';
                    }
                },

                getCategoryName() {
                    const cat = this.categories.find(c => c.id == this.formData.category_id);
                    return cat ? `${cat.name_arabic} / ${cat.name_german}` : '';
                },

                getVendorName() {
                    const vendor = this.vendors.find(v => v.id == this.formData.vendor_id);
                    return vendor ? `${vendor.store_name_in_arabic} / ${vendor.store_name_in_german}` : '';
                },

                // Image Management
                toggleDeleteExistingImage(index) {
                    this.existingImages[index].markedForDelete = !this.existingImages[index].markedForDelete;
                },

                addNewImage() {
                    this.newImages.push({
                        file: null,
                        preview: null,
                        color: ''
                    });
                },

                removeNewImage(index) {
                    this.newImages.splice(index, 1);
                },

                handleNewImageUpload(event, index) {
                    const file = event.target.files[0];
                    if (file) {
                        this.newImages[index].file = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.newImages[index].preview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                // Variant Management
                toggleDeleteExistingVariant(index) {
                    this.existingVariants[index].markedForDelete = !this.existingVariants[index].markedForDelete;
                },

                addNewVariant() {
                    this.newVariants.push({
                        price: '',
                        cost: '',
                        stock: '',
                        tax: '',
                        expiry_date: '',
                        expiry_days: '',
                        attributes: [{ attribute_id: '', value: '', colorPicker: '#000000', patternPreview: null, patternFile: null }]
                    });
                },

                removeNewVariant(index) {
                    this.newVariants.splice(index, 1);
                },

                addNewAttribute(variantIndex) {
                    this.newVariants[variantIndex].attributes.push({
                        attribute_id: '',
                        value: '',
                        colorPicker: '#000000',
                        patternPreview: null,
                        patternFile: null
                    });
                },

                removeNewAttribute(variantIndex, attrIndex) {
                    if (this.newVariants[variantIndex].attributes.length > 1) {
                        this.newVariants[variantIndex].attributes.splice(attrIndex, 1);
                    }
                },

                onAttributeTypeChange(variantIndex, attrIndex) {
                    this.newVariants[variantIndex].attributes[attrIndex].value = '';
                    this.newVariants[variantIndex].attributes[attrIndex].colorPicker = '#000000';
                    this.newVariants[variantIndex].attributes[attrIndex].patternPreview = null;
                    this.newVariants[variantIndex].attributes[attrIndex].patternFile = null;
                },

                getAttributeType(attributeId) {
                    if (!attributeId) return 'text';
                    const attr = this.variantAttributes.find(a => a.id == attributeId);
                    return attr?.type || 'text';
                },

                handlePatternUpload(event, variantIndex, attrIndex) {
                    const file = event.target.files[0];
                    if (file) {
                        this.newVariants[variantIndex].attributes[attrIndex].patternFile = file;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.newVariants[variantIndex].attributes[attrIndex].patternPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                // Form Submission
                async submitForm() {
                    // Final validation
                    this.validateCurrentStep();
                    if (Object.keys(this.errors).length > 0) return;

                    this.isSubmitting = true;

                    try {
                        const formData = new FormData();
                        formData.append('_method', 'PUT');
                        formData.append('_token', '{{ csrf_token() }}');

                        // Basic Info
                        formData.append('name_arabic', this.formData.name_arabic);
                        formData.append('name_german', this.formData.name_german);
                        formData.append('description_arabic', this.formData.description_arabic || '');
                        formData.append('description_german', this.formData.description_german || '');
                        formData.append('category_id', this.formData.category_id);
                        formData.append('child_category_id', this.formData.child_category_id || '');
                        formData.append('vendor_id', this.formData.vendor_id);
                        formData.append('is_active', this.formData.is_active ? '1' : '0');

                        // Images to delete
                        const deleteImageIds = this.existingImages.filter(i => i.markedForDelete).map(i => i.id);
                        formData.append('delete_images', JSON.stringify(deleteImageIds));

                        // New images
                        this.newImages.forEach((img, index) => {
                            if (img.file) {
                                formData.append(`new_images[${index}][image]`, img.file);
                                formData.append(`new_images[${index}][color]`, img.color || '');
                            }
                        });

                        // Existing variants updates
                        this.existingVariants.forEach((variant) => {
                            if (!variant.markedForDelete) {
                                formData.append(`existing_variants[${variant.id}][price]`, variant.price);
                                formData.append(`existing_variants[${variant.id}][cost]`, variant.cost || '');
                                formData.append(`existing_variants[${variant.id}][stock]`, variant.stock);
                                formData.append(`existing_variants[${variant.id}][tax]`, variant.tax || '');
                                formData.append(`existing_variants[${variant.id}][expiry_date]`, variant.expiry_date || '');
                                formData.append(`existing_variants[${variant.id}][expiry_days]`, variant.expiry_days || '');
                                formData.append(`existing_variants[${variant.id}][is_active]`, variant.is_active ? '1' : '0');
                            }
                        });

                        // Variants to delete
                        const deleteVariantIds = this.existingVariants.filter(v => v.markedForDelete).map(v => v.id);
                        formData.append('delete_variants', JSON.stringify(deleteVariantIds));

                        // New variants
                        this.newVariants.forEach((variant, vIndex) => {
                            formData.append(`new_variants[${vIndex}][price]`, variant.price);
                            formData.append(`new_variants[${vIndex}][cost]`, variant.cost || '');
                            formData.append(`new_variants[${vIndex}][stock]`, variant.stock);
                            formData.append(`new_variants[${vIndex}][tax]`, variant.tax || '');
                            formData.append(`new_variants[${vIndex}][expiry_date]`, variant.expiry_date || '');
                            formData.append(`new_variants[${vIndex}][expiry_days]`, variant.expiry_days || '');

                            variant.attributes.forEach((attr, aIndex) => {
                                formData.append(`new_variants[${vIndex}][attributes][${aIndex}][attribute_id]`, attr.attribute_id);
                                
                                if (this.getAttributeType(attr.attribute_id) === 'pattern' && attr.patternFile) {
                                    formData.append(`new_variants[${vIndex}][attributes][${aIndex}][pattern_file]`, attr.patternFile);
                                } else {
                                    formData.append(`new_variants[${vIndex}][attributes][${aIndex}][value]`, attr.value);
                                }
                            });
                        });

                        const response = await fetch('{{ route("products.update", $product->id) }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.href = data.redirect || '{{ route("products.show", $product->id) }}';
                        } else {
                            this.errors = data.errors || { general: 'An error occurred while saving.' };
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        this.errors = { general: 'An unexpected error occurred. Please try again.' };
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</x-admin-layout>