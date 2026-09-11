<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('products.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        {{ __('Category Management') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Organize your menu items into structured POS categories
                    </p>
                </div>
            </div>

            <button onclick="openCreateCategoryModal()" class="inline-flex items-center px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Category
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Order</th>
                                <th class="py-3.5 px-4">Category Name</th>
                                <th class="py-3.5 px-4">Description</th>
                                <th class="py-3.5 px-4 text-center">Products Count</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($categories as $category)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-500">
                                        {{ $category->sort_order }}
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        {{ $category->name }}
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-500 text-xs">
                                        {{ $category->description ?? '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#f0f8f5] text-[#155d49]">
                                            {{ $category->products_count }} items
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 text-xs rounded-full font-bold {{ $category->is_active ? 'bg-emerald-100 text-[#155d49]' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button onclick="openEditCategoryModal({{ json_encode($category) }})" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        No categories found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div id="create-category-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
                <h3 class="font-bold text-gray-900 text-lg">Add New Category</h3>
                <button onclick="closeCreateCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('categories.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Pastries & Bakery" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-900" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Optional description..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateCategoryModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow transition">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
                <h3 class="font-bold text-gray-900 text-lg">Edit Category</h3>
                <button onclick="closeEditCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="edit-category-form" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Category Name</label>
                    <input type="text" id="edit-cat-name" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-900" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Description</label>
                    <textarea id="edit-cat-desc" name="description" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="edit-cat-order" name="sort_order" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="edit-cat-active" name="is_active" value="1" class="rounded text-[#155d49] focus:ring-[#155d49]">
                        <span class="text-sm font-bold text-gray-800">Active</span>
                    </label>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditCategoryModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow transition">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openCreateCategoryModal() {
            document.getElementById('create-category-modal').classList.remove('hidden');
        }
        function closeCreateCategoryModal() {
            document.getElementById('create-category-modal').classList.add('hidden');
        }

        function openEditCategoryModal(cat) {
            document.getElementById('edit-category-form').action = `/categories/${cat.id}`;
            document.getElementById('edit-cat-name').value = cat.name;
            document.getElementById('edit-cat-desc').value = cat.description || '';
            document.getElementById('edit-cat-order').value = cat.sort_order;
            document.getElementById('edit-cat-active').checked = cat.is_active == 1;
            document.getElementById('edit-category-modal').classList.remove('hidden');
        }
        function closeEditCategoryModal() {
            document.getElementById('edit-category-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
