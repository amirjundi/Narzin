import 'package:auto_height_grid_view/auto_height_grid_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/bussiness_logic/vendor_stats_cubits/vendor_stats_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/presentation_layer/main_app_vendor/products_screens/products_Forms/add_product_screens/add_product_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/products_screens/products_Forms/edit_product_screens/edit_product_screen.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bussiness_logic/login_cubits/login_cubit.dart';
import '../../../bussiness_logic/profile_cubits/profile_cubit.dart';
import '../../../widgets/image_widgets/insta_image_widget.dart';

class ProductsScreen extends StatelessWidget {
  ProductsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    // print(ScreenSizing.width * 0.43);
    return Stack(
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            BlocBuilder<ProfileCubit, ProfileState>(
              builder: (context, state) {
                return Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.grey[300],
                      radius: 29,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(100),
                        child: SizedBox(
                          height: 57,
                          width: 57,
                          child: InstaNetworkImageWidget(
                            imageUrl: 'https://admin.narzin.com/storage/${BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.storeLogo ?? ''}',
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        minTileHeight: kToolbarHeight * 1.2,
                        title: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 5.0),
                          child: Row(
                            children: [
                              Text(
                                "${S.of(context).welcome_message} ",
                                style: TextStyle(fontSize: 13, color: Colors.grey[700], fontWeight: FontWeight.w400),
                              ),
                              Text(
                                S.of(context).app_name,
                                style: TextStyle(fontSize: 15, color: Colors.grey[700]),
                              ),
                            ],
                          ),
                        ),
                        subtitle: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 5.0),
                          child: Text(
                            context.read<ProfileCubit>().profile?.data?.user?.name ?? 'Not available',
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ),
                        trailing: IconButton(
                          style: IconButton.styleFrom(
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10), side: BorderSide(color: Colors.grey[300]!)),
                          ),
                          onPressed: () {},
                          icon: const Icon(
                            Icons.notifications_active_outlined,
                            size: 30,
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
            const SizedBox(
              height: 15,
            ),
            TextFormField(
              decoration: InputDecoration(
                prefixIcon: const Icon(Icons.search),
                hintText: S.of(context).search_placeholder,
                hintStyle: TextStyle(color: Colors.grey[500], fontSize: 14),
                contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(40),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(40),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
              ),
            ),
            const SizedBox(
              height: 15,
            ),
            Expanded(
              child: DefaultTabController(
                length: 2,
                child: Column(
                  children: [
                    SizedBox(
                      height: 32,
                      child: TabBar(
                        automaticIndicatorColorAdjustment: true,
                        tabs: [
                          Text(
                            S.of(context).status_active,
                            style: const TextStyle(fontSize: 20),
                          ),
                          Text(
                            S.of(context).status_pending,
                            style: const TextStyle(fontSize: 20),
                          ),
                        ],
                        indicator: BoxDecoration(color: Constants.mainColor, borderRadius: const BorderRadius.only(topRight: Radius.circular(20), topLeft: Radius.circular(20))),
                        indicatorSize: TabBarIndicatorSize.tab,
                        indicatorPadding: const EdgeInsets.only(top: 27, left: 10, right: 10),
                      ),
                    ),
                    const SizedBox(
                      height: 20,
                    ),
                    Expanded(
                      child: TabBarView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: [
                          ProductPage(isActive: true,),
                          ProductPage(isActive: false,),
                        ], // Disable manual swipe
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
        Positioned(
            bottom: 10,
            right: 0,
            child: BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
              builder: (productContext, state) {
                bool isLoading = productContext.read<ProductManipulationCubit>().isLoading;
                return InkWell(
                  borderRadius: BorderRadius.circular(20),
                  splashColor: Constants.lighterSecondaryColor,
                  onTap: () async {
                    productContext.read<ProductManipulationCubit>().resetBasicForm();
                    var res = await productContext.read<ProductManipulationCubit>().getCategories();
                    if (res == null) {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => AddProductScreen(),
                        ),
                      );
                    }
                  },
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(100),
                    child: isLoading
                        ? Stack(
                            alignment: Alignment.center,
                            children: [
                              SvgPicture.asset(
                                Assets.appIconsAddProductForLoading,
                              ),
                              const Positioned(
                                  child: CircularProgressIndicator(
                                color: Colors.white,
                              )),
                            ],
                          )
                        : SvgPicture.asset(
                            Assets.appIconsAddProductAsset,
                          ),
                  ),
                );
              },
            )),
      ],
    );
  }
}

class ProductPage extends StatelessWidget {
  ProductPage({super.key,required this.isActive});

  List<String> statsItems = [];
  List<String> filtersTitles = [];
  List<String> filtersIcons = [];
  bool isActive;


  // 'https://s3-alpha-sig.figma.com/img/dcb4/3283/bd2beb7b7955ad34ed519ba8683d54cb?Expires=1734307200&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=XmB13q9RR3n2DL1UN1AeOoKvd90brqZCjqqwcP3OfWZFLVSjhpQsdUjD--P2db~Gs3fKI7YuaeaqLieiKPJzMD3Xhv2zdbY9PYTfNq6nqyhFYUC9Lj~ssw3dudQPwVtY~GNI-1kJ7z6xcj2IEZVGQ24oemoNI78L~mJ2Zs7MVJ55~wqfr2fmBL0~NdsyhDSRWtHkk-UoB~-MFMcCagbSqTomwq7hU2w~i9d9pZcwTivsBmyw6A3GF~JdLT3JvGwY5wfHFwqepp2K7qZTrhWx2lR8J~P0gWjVHqoZZ3b8RT5gC54-TO24gDiL3CSLWnATnH27jdlJWzosNMWSV6YIJw__';

  @override
  Widget build(BuildContext context) {
    statsItems = [S.of(context).daily, S.of(context).weekly, S.of(context).monthly];
    filtersTitles = [
      S.of(context).status_new,
      S.of(context).status_active,
      S.of(context).status_completed,
      S.of(context).status_cancelled,
    ];
    filtersIcons = [
      Assets.appIconsNewIcon,
      Assets.appIconsActiveIcon,
      Assets.appIconsCompletedIcon,
      Assets.appIconsCancelIcon,
    ];
    return Column(
      children: [
        Container(
          height: 35,
          width: ScreenSizing.width,
          child: BlocBuilder<VendorStatsCubit, VendorStatsState>(
            builder: (context, state) {
              return BlocBuilder<ProductCubit, ProductState>(
                builder: (productContext, state) {
                  bool isLoading = productContext.read<ProductCubit>().isLoading;
                  return isLoading?Container() : ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: productContext.read<ProductCubit>().categories?.data?.length,
                    itemBuilder: (context, index) {
                      String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                      String? category_name = locale == 'ar'?(productContext.read<ProductCubit>().categories?.data?[index].nameArabic):(productContext.read<ProductCubit>().categories?.data?[index].nameGerman);
                      return InkWell(
                        onTap: () {
                          productContext.read<ProductCubit>().setSelectedFilterIndex(index);
                        },
                        child: Container(
                          margin: const EdgeInsets.only(left: 7),
                          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 5),
                          height: 40,
                          constraints: const BoxConstraints(minWidth: 100),
                          decoration: productContext.read<ProductCubit>().selectedFilterIndex == index
                              ? BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      Color(0xff3084C2),
                                      Color(0xff5BB5EF),
                                    ],
                                  ),
                                  borderRadius: BorderRadius.circular(30))
                              : BoxDecoration(
                                  borderRadius: BorderRadius.circular(30),
                                  border: Border.all(
                                    color: Colors.grey[300]!,
                                  ),
                                ),
                          child: Center(
                            child: Text(
                                category_name??'',
                              style: productContext.read<ProductCubit>().selectedFilterIndex == index ? const TextStyle(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600) : const TextStyle(fontSize: 17, color: Color(0xff5BB5EF)),
                            ),
                          ),
                        ),
                      );
                    },
                  );
                },
              );
            },
          ),
        ),
        const SizedBox(
          height: 10,
        ),
        Row(
          children: [
            SvgPicture.asset(Assets.appIconsTools),
            Text(
              S.of(context).sort_by,
              style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w600),
            ),
            BlocBuilder<VendorStatsCubit, VendorStatsState>(
              builder: (context, state) {
                return DropdownButton<String>(
                  padding: EdgeInsets.zero,
                  isDense: true,
                  items: statsItems
                      .map((e) => DropdownMenuItem(
                            value: e,
                            child: Text(
                              e,
                              style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w600),
                            ),
                          ))
                      .toList(),
                  underline: const SizedBox(),
                  value: context.read<VendorStatsCubit>().selectedDropDownVal,
                  onChanged: (value) {
                    context.read<VendorStatsCubit>().setSelectedValue(value);
                  },
                );
              },
            ),
          ],
        ),
        const SizedBox(
          height: 10,
        ),
        Expanded(
          child: BlocBuilder<ProductCubit, ProductState>(
            builder: (productContext, state) {
              bool isLoading = productContext.read<ProductCubit>().isLoading;
              var selectedCategory = productContext.read<ProductCubit>().filteredCategory;
              if (isLoading) {
                return Shimmer.fromColors(
                  highlightColor: Colors.white,
                  baseColor: Colors.grey[300]!,
                  child: AutoHeightGridView(
                    itemCount: 6,
                    crossAxisCount: 2,
                    mainAxisSpacing: 10,
                    crossAxisSpacing: 10,
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.all(0),
                    shrinkWrap: true,
                    builder: (context, index) {
                      return ProductItem(
                        productImage: '',
                        IconWidget: IconButton(
                            style: IconButton.styleFrom(
                              backgroundColor: const Color(0xffffffff),
                              padding: EdgeInsets.zero,
                              maximumSize: const Size(35, 35),
                              minimumSize: const Size(35, 35),
                            ),
                            padding: EdgeInsets.zero,
                            onPressed: () {},
                            icon: const Icon(
                              Icons.more_horiz,
                              color: Colors.black,
                            )),
                        productName: 'Product $index',
                      );
                    },
                  ),
                );
              }
              else {
                return AutoHeightGridView(
                  itemCount: productContext.read<ProductCubit>().filteredVendorProducts?.data?.length ?? 0,
                  crossAxisCount: 2,
                  mainAxisSpacing: 10,
                  crossAxisSpacing: 10,
                  physics: const BouncingScrollPhysics(),
                  padding: const EdgeInsets.all(0),
                  shrinkWrap: true,
                  builder: (context, index) {
                    String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                    String? product_image = "${productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].images?.firstOrNull?.image}";
                    String? product_name = locale == 'ar' ? (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].nameArabic) : (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].nameGerman);
                    String? product_category = locale == 'ar' ? (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].category?.nameArabic) : (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].category?.nameGerman);
                    String? product_price = (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].minPrice);
                    // print(product_image);
                    int categId = productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].category?.parentId??productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].category?.id??0;
                    if(isActive && (productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].isActive??false)){
                      bool isProductLoading = productContext.read<ProductCubit>().isSingleProductLoading[index];
                      return
                      //#############################[Unlock This if you want to filter by category]##############################################
                        // int.tryParse(selectedCategory?.parentId??selectedCategory?.id??'0') ==  categId?
                      ProductItem(
                        productImage: product_image,
                        IconWidget: IconButton(
                            style: IconButton.styleFrom(
                              backgroundColor: const Color(0xffffffff),
                              padding: EdgeInsets.zero,
                              maximumSize: const Size(35, 35),
                              minimumSize: const Size(35, 35),
                            ),
                            padding: EdgeInsets.zero,
                            onPressed: () async {
                              productContext.read<ProductManipulationCubit>().resetBasicForm();
                              var res2 = await productContext.read<ProductManipulationCubit>().getCategories();
                              int id = productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].id??0;
                              var res = await productContext.read<ProductCubit>().getSingleProduct(id: id,index:index);

                              if(res == null && res2 == null){
                                var singleProduct = productContext.read<ProductCubit>().singleProduct;

                                if(singleProduct == null) {
                                  return;
                                }
                                await BlocProvider.of<ProductManipulationCubit>(context).populateFieldsForUpdate(singleProduct);
                                Navigator.push(context, MaterialPageRoute(builder: (_) => EditProductScreen(product: singleProduct,),));
                              }

                            },
                            icon: isProductLoading? const Padding(
                              padding: EdgeInsets.all(8.0),
                              child: CircularProgressIndicator(),
                            ) : const Icon(
                              Icons.more_horiz,
                              color: Colors.black,
                            )),
                        productName: product_name,
                        category: product_category,
                        priceFrom: product_price,
                      );
                    // :Container();
                    }else if(!isActive && !(productContext.read<ProductCubit>().filteredVendorProducts?.data?[index].isActive??false)){
                      return ProductItem(
                        productImage: product_image,
                        IconWidget: IconButton(
                            style: IconButton.styleFrom(
                              backgroundColor: const Color(0xffffffff),
                              padding: EdgeInsets.zero,
                              maximumSize: const Size(35, 35),
                              minimumSize: const Size(35, 35),
                            ),
                            padding: EdgeInsets.zero,
                            onPressed: () {},
                            icon: const Icon(
                              Icons.more_horiz,
                              color: Colors.black,
                            )),
                        productName: product_name,
                        category: product_category,
                        priceFrom: product_price,
                      );
                    }else{
                      return Container();
                    }

                  },
                );
              }
            },
          ),
        ),
      ],
    );
  }
}
