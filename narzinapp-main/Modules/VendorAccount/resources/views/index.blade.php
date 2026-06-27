<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 w-full">
        <div class="max-w-7xl mx-auto ">
            <h1 class="text-xl font-bold">الصفحة الرئيسية</h1>
            <div class="flex justify-between items-center border border-spacing-2 w-full mt-5">
                <h1 class="text-lg mt-2 mb-2 p-2 ">
                    الاحصائيات
                </h1>

                <select class="select  w-[6rem] max-w-xs bg-inherit">
                    <option disabled selected>يوميا</option>
                    <option>اسبوعيا</option>
                    <option>شهريا</option>
                    <option>سنويا</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-12">

                <div class=" p-4 rounded-lg shadow-md flex items-center">
                    <div class="m-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56"
                            fill="none">
                            <path opacity="0.21" fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.285156 28.1753V35.4666C0.285156 46.6195 9.32639 55.6607 20.4793 55.6607H27.7705H35.0618C46.2147 55.6607 55.2559 46.6195 55.2559 35.4666V28.1753V20.8841C55.2559 9.73116 46.2147 0.689941 35.0618 0.689941H27.7705H20.4793C9.32637 0.689941 0.285156 9.73118 0.285156 20.8841V28.1753Z"
                                fill="#4AD991" />
                            <path
                                d="M17.7917 38.1513H39.1693C39.9564 38.1513 40.5944 38.7894 40.5944 39.5765C40.5944 40.3636 39.9564 41.0017 39.1693 41.0017H16.3666C15.5795 41.0017 14.9414 40.3636 14.9414 39.5765V16.7738C14.9414 15.9867 15.5795 15.3486 16.3666 15.3486C17.1537 15.3486 17.7917 15.9867 17.7917 16.7738V38.1513Z"
                                fill="#4AD991" />
                            <path opacity="0.5"
                                d="M23.1094 32.0005C22.5711 32.5747 21.6692 32.6038 21.095 32.0654C20.5208 31.5271 20.4917 30.6252 21.03 30.051L26.3744 24.3503C26.895 23.795 27.7601 23.7468 28.3392 24.241L32.5573 27.8404L38.0531 20.879C38.5409 20.2613 39.437 20.1558 40.0548 20.6436C40.6726 21.1313 40.778 22.0275 40.2903 22.6452L33.8771 30.7687C33.3761 31.4032 32.4483 31.4945 31.8334 30.9697L27.5236 27.292L23.1094 32.0005Z"
                                fill="#4AD991" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-lg font-bold">المبيعات</h1>
                        <p class="text-2xl ">1000$</p>
                    </div>

                </div>

                <div class=" p-4 rounded-lg shadow-md flex items-center">
                    <div class="m-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56"
                            fill="none">
                            <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.535156 28.1753V35.4666C0.535156 46.6195 9.57639 55.6607 20.7293 55.6607H28.0205H35.3118C46.4647 55.6607 55.5059 46.6195 55.5059 35.4666V28.1753V20.8841C55.5059 9.73116 46.4647 0.689941 35.3118 0.689941H28.0205H20.7293C9.57637 0.689941 0.535156 9.73118 0.535156 20.8841V28.1753Z"
                                fill="#FF9066" />
                            <path opacity="0.78" fill-rule="evenodd" clip-rule="evenodd"
                                d="M26.7673 22.4849C26.7849 22.2562 26.9756 22.0796 27.205 22.0796H27.6228C27.8483 22.0796 28.0372 22.2505 28.0596 22.4749L28.6309 28.1875L32.6852 30.5042C32.8219 30.5824 32.9064 30.7278 32.9064 30.8854V31.2774C32.9064 31.5669 32.6311 31.7771 32.3518 31.7009L26.5377 30.1153C26.3346 30.0599 26.1994 29.868 26.2155 29.6581L26.7673 22.4849Z"
                                fill="#FF9066" />
                            <path opacity="0.901274" fill-rule="evenodd" clip-rule="evenodd"
                                d="M21.3273 14.389C21.0954 14.1127 20.6477 14.2185 20.564 14.5694L19.0527 20.9053C18.9845 21.1912 19.2111 21.4621 19.5046 21.4455L26.0224 21.0761C26.3832 21.0557 26.5662 20.6324 26.3339 20.3556L24.6589 18.3594C25.7261 17.9947 26.8578 17.8042 28.0199 17.8042C33.7544 17.8042 38.4032 22.453 38.4032 28.1876C38.4032 33.9222 33.7544 38.5709 28.0199 38.5709C22.2853 38.5709 17.6365 33.9222 17.6365 28.1876C17.6365 27.2249 17.7669 26.282 18.0211 25.3758L15.6687 24.7159C15.359 25.8201 15.1934 26.9845 15.1934 28.1876C15.1934 35.2715 20.936 41.0141 28.0199 41.0141C35.1038 41.0141 40.8464 35.2715 40.8464 28.1876C40.8464 21.1037 35.1038 15.3611 28.0199 15.3611C26.238 15.3611 24.541 15.7244 22.9988 16.3811L21.3273 14.389Z"
                                fill="#FF9066" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-lg font-bold">المبيعات</h1>
                        <p class="text-2xl ">1000$</p>
                    </div>

                </div>

                <div class=" p-4 rounded-lg shadow-md flex items-center">
                    <div class="m-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56"
                            fill="none">
                            <path opacity="0.21" fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.785156 28.1753V35.4666C0.785156 46.6195 9.82639 55.6607 20.9793 55.6607H28.2705H35.5618C46.7147 55.6607 55.7559 46.6195 55.7559 35.4666V28.1753V20.8841C55.7559 9.73116 46.7147 0.689941 35.5618 0.689941H28.2705H20.9793C9.82637 0.689941 0.785156 9.73118 0.785156 20.8841V28.1753Z"
                                fill="#8280FF" />
                            <path opacity="0.587821" fill-rule="evenodd" clip-rule="evenodd"
                                d="M19.7227 22.0674C19.7227 24.7661 21.9103 26.9537 24.6089 26.9537C27.3076 26.9537 29.4952 24.7661 29.4952 22.0674C29.4952 19.3688 27.3076 17.1812 24.6089 17.1812C21.9103 17.1812 19.7227 19.3688 19.7227 22.0674ZM31.939 26.9537C31.939 28.9777 33.5797 30.6185 35.6037 30.6185C37.6277 30.6185 39.2684 28.9777 39.2684 26.9537C39.2684 24.9298 37.6277 23.289 35.6037 23.289C33.5797 23.289 31.939 24.9298 31.939 26.9537Z"
                                fill="#8280FF" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M24.5832 29.397C18.8156 29.397 14.0837 32.3611 13.6102 38.1913C13.5844 38.5089 14.1917 39.1696 14.4981 39.1696H34.6775C35.5951 39.1696 35.6094 38.4311 35.5951 38.1923C35.2372 32.1983 30.4319 29.397 24.5832 29.397ZM42.2631 39.1696H37.5513V39.1695C37.5513 36.4199 36.6428 33.8824 35.1097 31.8409C39.2716 31.8859 42.6702 33.9898 42.9259 38.4366C42.9362 38.6157 42.9259 39.1696 42.2631 39.1696Z"
                                fill="#8280FF" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-lg font-bold">المبيعات</h1>
                        <p class="text-2xl ">1000$</p>
                    </div>

                </div>


            </div>


                <h1 class="text-xl font-bold mt-12">الطلبات الجديدة</h1>



                <div class="mt-3 ">

                    {{-- NEW ORDERS --}}

                    <div class="overflow-x-auto">
                        <table class="table">
                          <!-- head -->
                          <thead>
                            <tr>
                              <th>
                                <label>
                                  <input type="checkbox" class="checkbox" />
                                </label>
                              </th>
                              <th>Order</th>
                              <th>Date</th>
                              <th>Price</th>
                              <th> Actions </th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- row 1 -->
                            <tr>
                              <th>
                                <label>
                                  <input type="checkbox" class="checkbox" />
                                </label>
                              </th>
                              <td>
                                <div class="flex items-center gap-3">
                                  <div class="avatar">
                                    <div class="mask mask-squircle h-12 w-12">
                                      <img
                                        src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                        alt="Avatar Tailwind CSS Component" />
                                    </div>
                                  </div>
                                  <div>
                                    <div class="font-bold">#FP08-323</div>
                                    <div class="text-sm opacity-50">تشيرت</div>
                                  </div>
                                </div>
                              </td>
                              <td>
                                20-3-2022
                                <br />
                              </td>
                              <td>800$</td>
                              <th>
                                <button class="btn btn-primary btn-xs">view</button>
                                <button class="btn btn-warning text-white btn-xs">edit</button>
                                <button class="btn btn-error text-white btn-xs">cancel</button>
                              </th>
                            </tr>

                            <tr>
                                <th>
                                  <label>
                                    <input type="checkbox" class="checkbox" />
                                  </label>
                                </th>
                                <td>
                                  <div class="flex items-center gap-3">
                                    <div class="avatar">
                                      <div class="mask mask-squircle h-12 w-12">
                                        <img
                                          src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                          alt="Avatar Tailwind CSS Component" />
                                      </div>
                                    </div>
                                    <div>
                                      <div class="font-bold">#FP08-323</div>
                                      <div class="text-sm opacity-50">تشيرت</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  20-3-2022
                                  <br />
                                </td>
                                <td>800$</td>
                                <th>
                                  <button class="btn btn-primary btn-xs">view</button>
                                  <button class="btn btn-warning text-white btn-xs">edit</button>
                                  <button class="btn btn-error text-white btn-xs">cancel</button>
                                </th>
                              </tr>

                              <tr>
                                <th>
                                  <label>
                                    <input type="checkbox" class="checkbox" />
                                  </label>
                                </th>
                                <td>
                                  <div class="flex items-center gap-3">
                                    <div class="avatar">
                                      <div class="mask mask-squircle h-12 w-12">
                                        <img
                                          src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                          alt="Avatar Tailwind CSS Component" />
                                      </div>
                                    </div>
                                    <div>
                                      <div class="font-bold">#FP08-323</div>
                                      <div class="text-sm opacity-50">تشيرت</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  20-3-2022
                                  <br />
                                </td>
                                <td>800$</td>
                                <th>
                                  <button class="btn btn-primary btn-xs">view</button>
                                  <button class="btn btn-warning text-white btn-xs">edit</button>
                                  <button class="btn btn-error text-white btn-xs">cancel</button>
                                </th>
                              </tr>

                              <tr>
                                <th>
                                  <label>
                                    <input type="checkbox" class="checkbox" />
                                  </label>
                                </th>
                                <td>
                                  <div class="flex items-center gap-3">
                                    <div class="avatar">
                                      <div class="mask mask-squircle h-12 w-12">
                                        <img
                                          src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                          alt="Avatar Tailwind CSS Component" />
                                      </div>
                                    </div>
                                    <div>
                                      <div class="font-bold">#FP08-323</div>
                                      <div class="text-sm opacity-50">تشيرت</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  20-3-2022
                                  <br />
                                </td>
                                <td>800$</td>
                                <th>
                                  <button class="btn btn-primary btn-xs">view</button>
                                  <button class="btn btn-warning text-white btn-xs">edit</button>
                                  <button class="btn btn-error text-white btn-xs">cancel</button>
                                </th>
                              </tr>

                              <tr>
                                <th>
                                  <label>
                                    <input type="checkbox" class="checkbox" />
                                  </label>
                                </th>
                                <td>
                                  <div class="flex items-center gap-3">
                                    <div class="avatar">
                                      <div class="mask mask-squircle h-12 w-12">
                                        <img
                                          src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                          alt="Avatar Tailwind CSS Component" />
                                      </div>
                                    </div>
                                    <div>
                                      <div class="font-bold">#FP08-323</div>
                                      <div class="text-sm opacity-50">تشيرت</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  20-3-2022
                                  <br />
                                </td>
                                <td>800$</td>
                                <th>
                                  <button class="btn btn-primary btn-xs">view</button>
                                  <button class="btn btn-warning text-white btn-xs">edit</button>
                                  <button class="btn btn-error text-white btn-xs">cancel</button>
                                </th>
                              </tr>

                              <tr>
                                <th>
                                  <label>
                                    <input type="checkbox" class="checkbox" />
                                  </label>
                                </th>
                                <td>
                                  <div class="flex items-center gap-3">
                                    <div class="avatar">
                                      <div class="mask mask-squircle h-12 w-12">
                                        <img
                                          src="https://img.daisyui.com/images/profile/demo/2@94.webp"
                                          alt="Avatar Tailwind CSS Component" />
                                      </div>
                                    </div>
                                    <div>
                                      <div class="font-bold">#FP08-323</div>
                                      <div class="text-sm opacity-50">تشيرت</div>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  20-3-2022
                                  <br />
                                </td>
                                <td>800$</td>
                                <th>
                                  <button class="btn btn-primary btn-xs">view</button>
                                  <button class="btn btn-warning text-white btn-xs">edit</button>
                                  <button class="btn btn-error text-white btn-xs">cancel</button>
                                </th>
                              </tr>
                          </tbody>
                          <!-- foot -->
                        </table>
                      </div>



{{-- STATISTICS --}}





                </div>


        </div>
    </div>
</x-app-layout>
