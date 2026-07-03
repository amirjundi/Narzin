import 'package:auto_height_grid_view/auto_height_grid_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/Banners_cubits/banners_cubit.dart';
import 'package:narzin/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/search_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/addresses_model.dart';
import 'package:narzin/model_layer/products_model.dart';
import 'package:narzin/presentation_layer/main_app_user/address_screens/add_address_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/home_screens/search_screens/searchSecond.dart';
import 'package:narzin/presentation_layer/main_app_user/products_screens/product_details_screen.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/helpers.dart';
import '../../../widgets/app_infrastructure_widgets/product_item_widget.dart';
import 'blocks/home_blocks_view.dart';
import 'blocks/home_popup.dart';
import 'search_screens/search_first.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  String? token;

  @override
  void initState() {
    // TODO: implement initState
    BlocProvider.of<ProductsCubit>(context).getAllProducts();
    BlocProvider.of<ProductsCubit>(context).getCategories();
    token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token;
    if(token != null){
      BlocProvider.of<ProductsCubit>(context).getWishlist(token: token);
      BlocProvider.of<ProfileCubit>(context).getAddresses(token: token);
    }
    BlocProvider.of<BannersCubit>(context).getBanners(token: token);
    String locale = BlocProvider.of<LocalizationCubit>(context).locale;
    BlocProvider.of<HomeBlocksCubit>(context).getHomeBlocks(locale: locale);

    super.initState();
  }

  showAddressesMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      barrierColor: const Color(0x1F000000),
      backgroundColor: Colors.white,
      constraints: BoxConstraints(maxHeight: ScreenSizing.height * 0.6, minHeight: ScreenSizing.height * 0.4, minWidth: ScreenSizing.width),
      sheetAnimationStyle: AnimationStyle(
        duration: const Duration(milliseconds: 300),
      ),
      builder: (context) {
        return BlocBuilder<ProfileCubit, ProfileState>(
          builder: (context, state) {
            bool isLoading = context.read<ProfileCubit>().isLoading;
            List<AddressData> addresses = context.read<ProfileCubit>().addressesModel?.data ?? [];
            return Container(
              padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        S.of(context).set_delivery_location,
                        style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
                      ),
                      IconButton(
                          onPressed: () {
                            Navigator.canPop(context) ? Navigator.pop(context) : null;
                          },
                          icon: const Icon(Icons.close))
                    ],
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  Expanded(
                    child: addresses.isEmpty
                        ? Center(
                            child: Text(
                            S.of(context).no_saved_addresses,
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: Colors.grey[400]),
                          ))
                        : ListView.separated(
                            itemBuilder: (context, index) {
                              return InkWell(
                                onTap: () {
                                  context.read<ProfileCubit>().setSelectedGroup(addresses[index].id.toString(), addresses[index].title??'');
                                  context.read<ProfileCubit>().setIsAddressesDefault(token: token, id: addresses[index].id.toString());
                                },
                                child: Container(
                                  constraints: const BoxConstraints(minHeight: 70),
                                  width: ScreenSizing.width,
                                  decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), color: Colors.grey[200]),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: ListTile(
                                          title: Column(
                                            crossAxisAlignment: CrossAxisAlignment.stretch,
                                            children: [
                                              Text(addresses[index].title ?? 'Not available',style: const TextStyle(fontWeight: FontWeight.bold),),
                                              Text(addresses[index].address ?? 'Not available'),
                                            ],
                                          ),
                                          subtitle: Text('منزل$index'),
                                        ),
                                      ),
                                      Radio<String?>(
                                        onChanged: (value) {
                                          context.read<ProfileCubit>().setSelectedGroup(value ?? '', addresses[index].title ?? '');
                                        },
                                        value: addresses[index].id.toString(),
                                        groupValue: context.read<ProfileCubit>().selectedAddress,
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                            separatorBuilder: (context, index) => const SizedBox(
                                  height: 10,
                                ),
                            itemCount: addresses.length),
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  InkWell(
                    onTap: isLoading
                        ? null
                        : () async {
                            context.read<ProfileCubit>().resetEveryThing();
                            // await context.read<ProfileCubit>().getCoordinates();
                            // var res = await context.read<ProfileCubit>().getCountries(token: token);
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const AddAddressScreen(),
                                ),
                              );

                          },
                    child: Container(
                      height: 50,
                      width: ScreenSizing.width,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.grey[300]!),
                      ),
                      child: isLoading
                          ? const Center(
                              child: CircularProgressIndicator(),
                            )
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.add,
                                  color: Colors.grey[500]!,
                                ),
                                Text(
                                  S.of(context).add,
                                  style: TextStyle(color: Colors.grey[500]!, fontSize: 18),
                                )
                              ],
                            ),
                    ),
                  )
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildHeader(BuildContext context) {
    return BlocBuilder<ProfileCubit, ProfileState>(
      builder: (context, state) {
        return Row(
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            CircleAvatar(
              backgroundColor: Colors.grey[300],
              radius: 25,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(100),
                // child: const SizedBox(
                //   height: 49,
                //   width: 49,
                //   child: InstaNetworkImageWidget(
                //     imageUrl: '',
                //   ),
                // ),
              ),
            ),
            Expanded(
              child: ListTile(
                onTap: () {
                  showAddressesMenu(context);
                },
                contentPadding: EdgeInsets.zero,
                minTileHeight: kToolbarHeight * 1.2,
                title: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 5.0),
                  child: Row(
                    children: [
                      Text(
                        "${S.of(context).delivery_to} ",
                        style: TextStyle(fontSize: 13, color: Colors.grey[700], fontWeight: FontWeight.w400),
                      ),
                    ],
                  ),
                ),
                subtitle: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 5.0),
                  child: Row(
                    children: [
                      Icon(
                        Icons.wrong_location_outlined,
                        size: 17,
                        color: Colors.grey[600]!,
                      ),
                      Container(
                        constraints: BoxConstraints(maxWidth: ScreenSizing.width * 0.4),
                        child: Text(
                          (context.read<ProfileCubit>().showAddress ?? ''),
                          style: TextStyle(fontSize: 15, color: Colors.grey[600]!, fontWeight: FontWeight.w500),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(
                        width: 5,
                      ),
                      SvgPicture.asset(Assets.appIconsArrowDown),
                    ],
                  ),
                ),
                trailing: IconButton(
                  style: IconButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10), side: BorderSide(color: Colors.grey[300]!)),
                  ),
                  onPressed: () {
                    // Navigator.push(context, MaterialPageRoute(builder: (context) => const NotificationsScreen(),),);
                  },
                  icon: const Icon(
                    Icons.notifications_active_outlined,
                    size: 20,
                  ),
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildSearchBar(BuildContext context) {
    return InkWell(
      onTap: () {
        Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const SearchFirst(),
            ));
      },
      child: Hero(
        transitionOnUserGestures: true,
        tag: 'search',
        child: Material(
          color: Colors.transparent,
          child: TextFormField(
            enabled: false,
            readOnly: true,
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
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: BlocListener<HomeBlocksCubit, HomeBlocksState>(
        listener: (context, state) {
          if (state is HomeBlocksLoaded) {
            final blocks = context.read<HomeBlocksCubit>().blocks;
            maybeShowHomePopup(context, blocks);
          }
        },
        child: BlocBuilder<HomeBlocksCubit, HomeBlocksState>(
          builder: (context, state) {
            final blocksCubit = context.read<HomeBlocksCubit>();
            if (state is HomeBlocksLoaded && blocksCubit.blocks.isNotEmpty) {
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _buildHeader(context),
                  _buildSearchBar(context),
                  HomeBlocksView(blocks: blocksCubit.blocks),
                ],
              );
            }
            return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildHeader(context),
          const SizedBox(
            height: 20,
          ),
          Text(
            S.of(context).be_different,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
          ),
          Text(
            S.of(context).most_popular_products,
            style: const TextStyle(fontSize: 25, fontWeight: FontWeight.w600),
          ),
          Text(
            S.of(context).discover_fashion,
            style: const TextStyle(fontSize: 15, color: Color(0xff6B7280), fontWeight: FontWeight.w400),
          ),
          const SizedBox(
            height: 20,
          ),
          const CarouselSection(),
          const SizedBox(
            height: 20,
          ),
          _buildSearchBar(context),
          const SizedBox(
            height: 20,
          ),
          BlocBuilder<ProductsCubit, ProductsState>(
            builder: (context, state) {
              String locale = BlocProvider.of<LocalizationCubit>(context).locale;
              int length =context.read<ProductsCubit>().categories?.data?.length ?? 0;
              return Container(
                constraints:  BoxConstraints(minHeight:length == 0? 50: 120,maxHeight:length == 0? 50: 120),
                width: ScreenSizing.width,
                child: length == 0? Center(child: Text(S.of(context).no_categories_available,style: TextStyle(fontSize: 17,fontWeight: FontWeight.bold,color: Colors.grey[400]),)):
                Row(
                  children: [
                    Expanded(
                      child: ListView.separated(
                        itemBuilder: (context, index) {
                          int selectedItem = context.read<ProductsCubit>().selectedScreen;
                          String name = locale == 'ar' ? (context.read<ProductsCubit>().categories?.data?[index].nameArabic ?? S.of(context).category) : (context.read<ProductsCubit>().categories?.data?[index].nameGerman ?? S.of(context).category);
                          return InkWell(
                            onTap: () {
                              context.read<ProductsCubit>().changeSelectedIndex(index);
                              BlocProvider.of<SearchCubit>(context).resetFilters();
                              BlocProvider.of<SearchCubit>(context).selectedCategory = (context.read<ProductsCubit>().categories?.data?[index].id ?? 0).toString();
                              // BlocProvider.of<SearchCubit>(context).prepareFilteredSearchUrl();
                              BlocProvider.of<SearchCubit>(context).getSearchedProducts();
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SearchSecond(),
                                ),
                              );
                            },
                            child: Container(
                              padding: const EdgeInsets.all(10),
                              constraints: const BoxConstraints(minHeight: 120),
                              width: 100,
                              decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(18),
                                  color: Colors.white,
                              ),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.start,
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  Container(
                                    height:65,
                                    width: 65,
                                    decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(10),
                                        color: Colors.blue[200],
                                      image: DecorationImage(image: NetworkImage("${context.read<ProductsCubit>().categories?.data?[index].image}",),fit: BoxFit.cover)
                                      /////////////////////
                                    ),
                                  ),
                                  const SizedBox(
                                    height: 5,
                                  ),
                                  Expanded(
                                    child: Text(
                                      name,
                                      style: TextStyle(fontSize: 12, color: Colors.grey[400]),
                                      textAlign: TextAlign.center,
                                    ),
                                  )
                                ],
                              ),
                            ),
                          );
                        },
                        separatorBuilder: (context, index) => const SizedBox(
                          width: 15,
                        ),
                        itemCount: context.read<ProductsCubit>().categories?.data?.length ?? 0,
                        scrollDirection: Axis.horizontal,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(
            height: 10,
          ),
          BlocConsumer<ProductsCubit, ProductsState>(
            builder: (context, state) {
              print(ScreenSizing.width);
              bool isLoading = context.read<ProductsCubit>().isLoading;
              String locale = BlocProvider.of<LocalizationCubit>(context).locale;
              int selectedProductId = context.read<ProductsCubit>().selectedId;
              Map<int, bool> wishlistItems = Helpers.wishlistItems;
              if (isLoading) {
                return const LoadingWidget();
              } else {
                List<ProductData>? products = context.read<ProductsCubit>().products?.data?.data;
                int length = products?.length ?? 0;
                if (length == 0 || products == null) {
                  return Center(child: Text(S.of(context).no_products_available,style: TextStyle(fontSize: 17,fontWeight: FontWeight.bold,color: Colors.grey[400]),));
                }
                return AutoHeightGridView(
                  crossAxisCount: ScreenSizing.width > 1000
                      ? 6
                      : ScreenSizing.width > 900
                          ? 5
                          : ScreenSizing.width > 770
                              ? 4
                              : ScreenSizing.width > 450
                                  ? 3
                                  : 2,
                  padding: EdgeInsets.zero,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: length > 8 ? 8 : length,
                  builder: (context, index) {
                    String? productImage = products[index].images?.firstOrNull?.image;
                    String? productName = locale == 'ar' ? (products[index].nameArabic) : (products[index].nameGerman);
                    String? productCategory = locale == 'ar' ? (products[index].category?.nameArabic) : (products[index].category?.nameGerman);
                    String? productPrice = (products[index].minPrice);
                    int productId = (products[index].id ?? 0);
                    bool isFavorite = wishlistItems[productId] ?? false;
                    int? itemId = Helpers.wishlistProducts[productId];
                    String rating = products[index].averageRating.toString();
                    return ProductItem(
                      productImage: productImage,
                      productName: productName,
                      category: productCategory,
                      rating: rating.length > 3 ? rating.substring(0, 3) : rating,
                      priceFrom: productPrice,
                      icon: isFavorite ? Icons.favorite : Icons.favorite_border,
                      IconWidget: selectedProductId == productId
                          ? const Center(
                              child: Padding(
                                padding: EdgeInsets.all(8.0),
                                child: CircularProgressIndicator(
                                  color: Colors.red,
                                  strokeWidth: 2,
                                ),
                              ),
                            )
                          : null,
                      onIconPressed: () async {
                        if (!isFavorite) {
                          await context.read<ProductsCubit>().add2Wishlist(token: token, product_id: productId);
                        }else{
                          await context.read<ProductsCubit>().deleteFromWishlist(token: token, product_id: productId ?? 0, itemId: itemId??0);
                          await context.read<ProductsCubit>().getWishlist(token: token);
                        }
                      },
                      onTap: () {
                        context.read<ProductsCubit>().getSingleProduct(id: productId ?? 0);
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => ProductDetailsScreen(
                              minPrice: productPrice,
                              isSearch: false,
                            ),
                          ),
                        );
                      },
                    );
                  },
                );
              }
            },
            listener: (BuildContext context, ProductsState state) {
              if (state is ProductsSuccess) {}
            },
          ),
          const SizedBox(
            height: 20,
          ),
          const CarouselSection(),
          const SizedBox(
            height: 20,
          ),
          BlocBuilder<ProductsCubit, ProductsState>(
            builder: (context, state) {
              int selectedProductId = context.read<ProductsCubit>().selectedId;
              Map<int, bool> wishlistItems = Helpers.wishlistItems;
              int length = context.read<ProductsCubit>().products?.data?.data?.length ?? 0;
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        S.of(context).most_requested,
                        style: TextStyle(fontSize: 19, fontWeight: FontWeight.w600, color: Colors.grey[600]!),
                      ),
                      BlocBuilder<SearchCubit, SearchState>(
                        builder: (searchContext, state) {
                          return InkWell(
                            onTap:length == 0? null: () {
                              searchContext.read<SearchCubit>().resetFilters();
                              searchContext.read<SearchCubit>().getSearchedProducts();
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SearchSecond(),
                                ),
                              );
                            },
                            child: Row(
                              children: [
                                Text(
                                  S.of(context).more,
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Constants.mainColor),
                                ),
                                Icon(
                                  Icons.arrow_forward_rounded,
                                  color: Constants.mainColor,
                                  size: 23,
                                )
                              ],
                            ),
                          );
                        },
                      )
                    ],
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  SizedBox(
                    height: length == 0? 100: 250,
                    width: ScreenSizing.width,
                    child:length == 0?Center(child: Text(S.of(context).no_products_available,style: TextStyle(fontSize: 17,fontWeight: FontWeight.bold,color: Colors.grey[400]),)): Column(
                      children: [
                        Expanded(
                          child:  Row(
                            children: [
                              Expanded(
                                child: ListView.separated(
                                  itemBuilder: (context, index) {
                                    String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                                    String? productImage = context.read<ProductsCubit>().products?.data?.data?[index].images?.firstOrNull?.image;
                                    String? productName = locale == 'ar' ? (context.read<ProductsCubit>().products?.data?.data?[index].nameArabic) : (context.read<ProductsCubit>().products?.data?.data?[index].nameGerman);
                                    String? productCategory = locale == 'ar' ? (context.read<ProductsCubit>().products?.data?.data?[index].category?.nameArabic) : (context.read<ProductsCubit>().products?.data?.data?[index].category?.nameGerman);
                                    String? productPrice = (context.read<ProductsCubit>().products?.data?.data?[index].minPrice);
                                    int? productId = (context.read<ProductsCubit>().products?.data?.data?[index].id);
                                    bool isFavorite = wishlistItems[productId] ?? false;
                                    int? itemId = Helpers.wishlistProducts[productId];
                                    return InkWell(
                                      onTap: () {
                                        context.read<ProductsCubit>().getSingleProduct(id: productId ?? 0);
                                        Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) => const ProductDetailsScreen(
                                                isSearch: false,
                                              ),
                                            ));
                                      },
                                      child: ProductItem2(
                                        productImage: productImage,
                                        productName: productName,
                                        category: productCategory,
                                        priceFrom: productPrice,
                                        icon: isFavorite ? Icons.favorite : Icons.favorite_border,
                                        IconWidget: selectedProductId == productId
                                            ? const Center(
                                                child: Padding(
                                                  padding: EdgeInsets.all(8.0),
                                                  child: CircularProgressIndicator(
                                                    color: Colors.red,
                                                    strokeWidth: 2,
                                                  ),
                                                ),
                                              )
                                            : null,
                                        onPressed: () async {
                                          if (!isFavorite) {
                                            await context.read<ProductsCubit>().add2Wishlist(token: token, product_id: productId ?? 0);
                                          }else{
                                            await context.read<ProductsCubit>().deleteFromWishlist(token: token, product_id: productId ?? 0, itemId: itemId??0);
                                            await context.read<ProductsCubit>().getWishlist(token: token);
                                          }
                                        },
                                      ),
                                    );
                                  },
                                  separatorBuilder: (context, index) => const SizedBox(
                                    width: 10,
                                  ),
                                  itemCount: context.read<ProductsCubit>().products?.data?.data?.length ?? 0,
                                  scrollDirection: Axis.horizontal,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  )
                ],
              );
            },
          ),
          const SizedBox(
            height: 20,
          ),
          BlocBuilder<ProductsCubit, ProductsState>(
            builder: (context, state) {
              int selectedProductId = context.read<ProductsCubit>().selectedId;
              Map<int, bool> wishlistItems = Helpers.wishlistItems;
              int length = context.read<ProductsCubit>().products?.data?.data?.length ?? 0;
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        S.of(context).most_popular,
                        style: TextStyle(fontSize: 19, fontWeight: FontWeight.w600, color: Colors.grey[600]!),
                      ),
                      BlocBuilder<SearchCubit, SearchState>(
                        builder: (searchContext, state) {
                          return InkWell(
                            onTap: length == 0? null: () {
                              searchContext.read<SearchCubit>().resetFilters();
                              searchContext.read<SearchCubit>().getSearchedProducts();
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const SearchSecond(),
                                ),
                              );
                            },
                            child: Row(
                              children: [
                                Text(
                                  S.of(context).more,
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Constants.mainColor),
                                ),
                                Icon(
                                  Icons.arrow_forward_rounded,
                                  color: Constants.mainColor,
                                  size: 23,
                                )
                              ],
                            ),
                          );
                        },
                      )
                    ],
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  SizedBox(
                    height:length == 0?100: 250,
                    width: ScreenSizing.width,
                    child: length == 0?Center(child: Text(S.of(context).no_products_available,style: TextStyle(fontSize: 17,fontWeight: FontWeight.bold,color: Colors.grey[400]),)): Column(
                      children: [
                        Expanded(
                          child: Row(
                            children: [
                              Expanded(
                                child: ListView.separated(
                                  itemBuilder: (context, index) {
                                    String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                                    String? productImage = context.read<ProductsCubit>().products?.data?.data?[index].images?.firstOrNull?.image;
                                    String? productName = locale == 'ar' ? (context.read<ProductsCubit>().products?.data?.data?[index].nameArabic) : (context.read<ProductsCubit>().products?.data?.data?[index].nameGerman);
                                    String? productCategory = locale == 'ar' ? (context.read<ProductsCubit>().products?.data?.data?[index].category?.nameArabic) : (context.read<ProductsCubit>().products?.data?.data?[index].category?.nameGerman);
                                    String? productPrice = (context.read<ProductsCubit>().products?.data?.data?[index].minPrice);
                                    int? productId = (context.read<ProductsCubit>().products?.data?.data?[index].id);
                                    bool isFavorite = wishlistItems[productId] ?? false;
                                    int? itemId = Helpers.wishlistProducts[productId];
                                    return InkWell(
                                      onTap: () {
                                        context.read<ProductsCubit>().getSingleProduct(id: productId ?? 0);
                                        Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) => const ProductDetailsScreen(
                                                isSearch: false,
                                              ),
                                            ));
                                      },
                                      child: ProductItem2(
                                        productImage: productImage,
                                        productName: productName,
                                        category: productCategory,
                                        priceFrom: productPrice,
                                        icon: isFavorite ? Icons.favorite : Icons.favorite_border,
                                        IconWidget: selectedProductId == productId
                                            ? const Center(
                                                child: Padding(
                                                  padding: EdgeInsets.all(8.0),
                                                  child: CircularProgressIndicator(
                                                    color: Colors.red,
                                                    strokeWidth: 2,
                                                  ),
                                                ),
                                              )
                                            : null,
                                        onPressed: () async {
                                          if (!isFavorite) {
                                            await context.read<ProductsCubit>().add2Wishlist(token: token, product_id: productId ?? 0);
                                          }else{
                                            await context.read<ProductsCubit>().deleteFromWishlist(token: token, product_id: productId ?? 0, itemId: itemId??0);
                                            await context.read<ProductsCubit>().getWishlist(token: token);
                                          }
                                        },
                                      ),
                                    );
                                  },
                                  separatorBuilder: (context, index) => const SizedBox(
                                    width: 10,
                                  ),
                                  itemCount: context.read<ProductsCubit>().products?.data?.data?.length ?? 0,
                                  scrollDirection: Axis.horizontal,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  )
                ],
              );
            },
          ),
        ],
      );
          },
        ),
      ),
    );
  }
}

class AddressLoadingWidget extends StatelessWidget {
  const AddressLoadingWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[400]!,
      highlightColor: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SizedBox(
            height: 30,
            width: ScreenSizing.width,
          ),
          SizedBox(
            height: 20,
            width: ScreenSizing.width,
          ),
          SizedBox(
            height: 40,
            width: ScreenSizing.width,
          ),
        ],
      ),
    );
  }
}

class LoadingWidget extends StatelessWidget {
  const LoadingWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      highlightColor: Colors.white,
      baseColor: Colors.grey[300]!,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              ProductItem(
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
                productName: 'Product',
              ),
              const SizedBox(width: 5),
              ProductItem(
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
                productName: 'Product',
              ),
            ],
          ),
          const SizedBox(
            height: 5,
          ),
          Row(
            children: [
              ProductItem(
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
                productName: 'Product',
              ),
              const SizedBox(width: 5),
              ProductItem(
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
                productName: 'Product',
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class CarouselSection extends StatelessWidget {
  const CarouselSection({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<BannersCubit, BannersState>(
      builder: (context, state) {
        int length = (context.read<BannersCubit>().bannersModel?.data?.length??0);
        bool isLoading = context.read<BannersCubit>().isLoading;
        if(isLoading){
          return SizedBox(
            height: 100,
            width: ScreenSizing.width,
            child: const Center(child: CircularProgressIndicator()),
          );

        }else{
          if(length == 0){
            return Container(height: 10,);
          }
          return ConstrainedBox(
            constraints: BoxConstraints(
              maxHeight: 160,
              maxWidth: ScreenSizing.width,
              minWidth: ScreenSizing.width,
            ),
            child: CarouselView(
              elevation: 3,
              // onTap: (value) {},
              scrollDirection: Axis.horizontal,
              itemExtent: ScreenSizing.width,
              children: [
                for (int i2 = 0; i2 < length; i2++)
                  SizedBox(
                      width: ScreenSizing.width,
                      child:InstaNetworkImageWidget(imageUrl: context.read<BannersCubit>().bannersModel?.data?[i2].image??'',errorImage: Assets.imagesSpecialCard,)
                  ),
              ],
            ),
          );
        }

  },
);
  }
}
