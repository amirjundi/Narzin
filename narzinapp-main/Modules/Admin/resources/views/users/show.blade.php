<x-admin-layout>
    <div class="min-h-screen bg-gray-50/50 p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- User Profile Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative h-48 bg-gradient-to-r from-blue-500 to-indigo-600">
                    <div class="absolute -bottom-20 left-8">
                        <div class="w-32 h-32 rounded-full border-4 border-white bg-gray-200 flex items-center justify-center">
                            <span class="text-4xl text-gray-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="pt-24 pb-6 px-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-gray-500">{{ $user->email }}</p>
                        </div>
                        <div class="flex gap-4">
                            <a href="{{route('users.edit' ,  $user->id)}}" class="btn btn-primary">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- Wallet Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Wallet Balance</h2>
                    <div class="badge badge-primary badge-lg">Active</div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Current Balance</div>
                            <div class="stat-value text-primary">IQD{{ number_format($user->wallet->balance ?? 0, 0) }}</div>
                            <div class="stat-desc">Last updated: {{ $user->wallet?->updated_at?->diffForHumans() ?? "Date" }}</div>
                        </div>
                    </div>
                    <button class="btn btn-outline btn-primary" onclick="add_funds_modal.showModal()">Manage Balance</button>
                </div>
            </div>
    
            <!-- Order History Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Order History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="bg-gray-50">Order ID</th>
                                <th class="bg-gray-50">Date</th>
                                <th class="bg-gray-50">Total Spent</th>
                                <th class="bg-gray-50">Status</th>
                                <th class="bg-gray-50">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders as $order)
                                <tr>
                                    <td class="font-medium">#{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>${{ number_format($order->final_amount, 2) }}</td>
                                    <td>
                                        <div class="badge badge-{{ $order->order_status === 'completed' ? 'success' : 'warning' }} gap-2">
                                            {{ ucfirst($order->order_status) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{route("orders.show" , $order->id)}}" class="btn btn-sm btn-ghost">View</a>
                                            <a  href="{{route('order.print' , $order->id)}}" class="btn btn-sm btn-ghost">Invoice</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        No orders found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">My Addresses</h2>
                    <button class="btn btn-primary" onclick="add_address_modal.showModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add New Address
                    </button>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($user->address as $address)
                        <div class="border rounded-lg p-4 relative">
                            <!-- Address Title or Default Label -->
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-semibold text-lg">
                                    {{ $address->title ?? 'Address ' . $loop->iteration }}
                                    @if($address->is_default)
                                        <span class="ml-2 badge badge-primary">Default</span>
                                    @endif
                                </h3>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-sm btn-ghost">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><button onclick="edit_address_modal_{{ $address->id }}.showModal()">Edit</button></li>
                                        <li>
                                            <form action="{{ route('address.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error">Delete</button>
                                            </form>
                                        </li>
                                        @if(!$address->is_default)
                                            <li>
                                                <form action="{{ route('address.set-default', $address->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit">Set as Default</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
            
                            <!-- Address Details -->
                            <div class="space-y-2 text-gray-700">
                                <p>{{ $address->address }}</p>
                                <p>{{ $address->city->name ?? 'City' }}, {{ $address->country->name ?? 'Country' }} {{ $address->postal_code }}</p>
                                <p>{{ $address->phone_number }}</p>
                            </div>
                            
                            <!-- Edit Address Modal for this address -->
                            <dialog id="edit_address_modal_{{ $address->id }}" class="modal">
                                <div class="modal-box">
                                    <form method="dialog">
                                        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                                    </form>
                                    <h3 class="font-bold text-lg mb-4">Edit Address</h3>
                                    
                                    <form action="{{ route('address.update', $address->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        
                                        <!-- Address Title -->
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text">Address Title</span>
                                            </label>
                                            <input type="text" name="title" value="{{ $address->title }}" 
                                                  placeholder="Home, Work, etc." class="input input-bordered w-full" />
                                        </div>
                                        
                                        <!-- Address Line -->
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text">Address</span>
                                            </label>
                                            <input type="text" name="address" value="{{ $address->address }}" 
                                                  placeholder="Street address" class="input input-bordered w-full" required />
                                        </div>
                                        
                                        <!-- Country and City Selection -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="form-control">
                                                <label class="label">
                                                    <span class="label-text">Country</span>
                                                </label>
                                                <select name="country_id" class="select select-bordered w-full" required>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" {{ $address->country_id == $country->id ? 'selected' : '' }}>
                                                            {{ $country->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="form-control">
                                                <label class="label">
                                                    <span class="label-text">City</span>
                                                </label>
                                                <select name="city_id" class="select select-bordered w-full" required>
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}" {{ $address->city_id == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Postal Code and Phone -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="form-control">
                                                <label class="label">
                                                    <span class="label-text">Postal Code</span>
                                                </label>
                                                <input type="text" name="postal_code" value="{{ $address->postal_code }}" 
                                                      placeholder="Postal/ZIP code" class="input input-bordered w-full" required />
                                            </div>
                                            
                                            <div class="form-control">
                                                <label class="label">
                                                    <span class="label-text">Phone Number</span>
                                                </label>
                                                <input type="text" name="phone_number" value="{{ $address->phone_number }}" 
                                                      placeholder="Contact phone" class="input input-bordered w-full" required />
                                            </div>
                                        </div>
                                        
                                        <div class="modal-action">
                                            <button type="submit" class="btn btn-primary">Update Address</button>
                                            <button type="button" class="btn btn-ghost" 
                                                    onclick="edit_address_modal_{{ $address->id }}.close()">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </dialog>
                        </div>
                    @empty
                        <div class="col-span-2 p-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-gray-500 mb-4">You haven't added any addresses yet</p>
                                <button class="btn btn-primary" onclick="add_address_modal.showModal()">
                                    Add Your First Address
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Add New Address Modal -->
            <dialog id="add_address_modal" class="modal">
                <div class="modal-box">
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                    </form>
                    <h3 class="font-bold text-lg mb-4">Add New Address</h3>
                    
                    <form action="{{ route('address.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <!-- Address Title -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Address Title (Optional)</span>
                            </label>
                            <input type="text" name="title" placeholder="Home, Work, etc." class="input input-bordered w-full" />
                        </div>
                        
                        <!-- Address Line -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Address</span>
                            </label>
                            <input type="text" name="address" placeholder="Street address" class="input input-bordered w-full" required />
                        </div>
                        
                        <!-- Country and City Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Country</span>
                                </label>
                                <select name="country_id" class="select select-bordered w-full" required>
                                    <option value="" disabled selected>Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">City</span>
                                </label>
                                <select name="city_id" class="select select-bordered w-full" required>
                                    <option value="" disabled selected>Select City</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Postal Code and Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Postal Code</span>
                                </label>
                                <input type="text" name="postal_code" placeholder="Postal/ZIP code" class="input input-bordered w-full" required />
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Phone Number</span>
                                </label>
                                <input type="text" name="phone_number" placeholder="Contact phone" class="input input-bordered w-full" required />
                            </div>
                        </div>
                        
                        <div class="modal-action">
                            <button type="submit" class="btn btn-primary">Add Address</button>
                            <button type="button" class="btn btn-ghost" onclick="add_address_modal.close()">Cancel</button>
                        </div>
                    </form>
                </div>
            </dialog>
        </div>
    </div>
    
    <!-- Manage Balance Modal -->
    <dialog id="add_funds_modal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4">Manage Wallet Balance</h3>
            
            <form action="{{ route('wallet.update-balance', $user->id) }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Current Balance Display -->
                <div class="bg-base-200 p-4 rounded-lg mb-4">
                    <div class="text-sm text-gray-600">Current Balance</div>
                    <div class="text-2xl font-bold">IQD{{ number_format($user->wallet->balance ?? 0, 0) }}</div>
                </div>
    
                <!-- Operation Type -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Operation</span>
                    </label>
                    <select name="operation" class="select select-bordered w-full" required>
                        <option value="add">Add Funds</option>
                        <option value="subtract">Subtract Funds</option>
                    </select>
                </div>
    
                <!-- Amount Input -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Amount ($)</span>
                    </label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="Enter amount" 
                           class="input input-bordered w-full" required />
                </div>
                <input type="text" name="user_id" value="{{$user->id}}" hidden id="">
                <!-- Notes -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Notes (Optional)</span>
                    </label>
                    <textarea name="notes" class="textarea textarea-bordered h-24" 
                              placeholder="Add any additional notes"></textarea>
                </div>
    
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary">Update Balance</button>
                    <button type="button" class="btn btn-ghost" onclick="add_funds_modal.close()">Cancel</button>
                </div>
            </form>
        </div>
    </dialog>
    </x-admin-layout>