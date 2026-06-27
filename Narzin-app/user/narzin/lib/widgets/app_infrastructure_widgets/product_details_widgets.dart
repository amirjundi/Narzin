import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';
import 'package:shimmer/shimmer.dart';

import '../../bussiness_logic/cart_cubits/cart_cubit.dart';
import '../../bussiness_logic/localization_cubit/localization_cubit.dart';
import '../../bussiness_logic/login_cubits/login_cubit.dart';
import '../../bussiness_logic/product_cubits/product_cubit.dart';
import '../../bussiness_logic/product_cubits/search_cubit.dart';
import '../../core/constants.dart';
import '../../core/helpers.dart';
import '../../core/screen_sizing_constants.dart';
import '../../generated/assets.dart';
import '../../generated/l10n.dart';
import '../../model_layer/single_product_model.dart';
import '../../model_layer/vendor_data_model.dart';
import '../../presentation_layer/main_app_user/products_screens/product_details_screen.dart';
import '../image_widgets/insta_image_widget.dart';

class VendorWidget extends StatelessWidget {
  const VendorWidget({super.key, required this.vendor, required this.locale});

  final VendorDataModel? vendor;
  final String locale;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const SizedBox(
            width: 20,
          ),
          CircleAvatar(
            radius: 35,
            child: InstaNetworkImageWidget(
              imageUrl: '${vendor?.data?.storeLogo ?? ''}',
            ),
          ),
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Material(
                child: ListTile(
                  minTileHeight: 90,
                  tileColor: Colors.white,
                  title: Text(locale == 'ar' ? (vendor?.data?.storeNameInArabic ?? 'Not available') : (vendor?.data?.storeNameInGerman ?? 'Not available')),
                  subtitle: Row(
                    children: [
                      Image.asset(
                        Assets.appIconsRateIcon,
                        height: 20,
                      ),
                      const SizedBox(
                        width: 5,
                      ),
                      const Text('4.7',
                          style: TextStyle(
                            fontSize: 15,
                          ))
                    ],
                  ),
                ),
              ),
            ),
          )
        ],
      ),
    );
  }
}

class VendorLoadingWidget extends StatelessWidget {
  const VendorLoadingWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[200]!,
      highlightColor: Colors.white,
      child: Container(
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            const SizedBox(
              width: 20,
            ),
            const CircleAvatar(
              radius: 35,
            ),
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: Material(
                  child: ListTile(
                    minTileHeight: 90,
                    tileColor: Colors.white,
                    title: const Text('VENDOR NAME'),
                    subtitle: Row(
                      children: [
                        Image.asset(
                          Assets.appIconsRateIcon,
                          height: 20,
                        ),
                        const SizedBox(
                          width: 5,
                        ),
                        const Text('4.7',
                            style: TextStyle(
                              fontSize: 15,
                            ))
                      ],
                    ),
                  ),
                ),
              ),
            )
          ],
        ),
      ),
    );
  }
}

class ProductsSuggested extends StatelessWidget {
  const ProductsSuggested({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ProductsCubit, ProductsState>(
      builder: (context, state) {
        bool isWishlistLoading = context.read<ProductsCubit>().isWishlistLoading;
        int selectedProductId = context.read<ProductsCubit>().selectedId;
        Map<int, bool> wishlistItems = Helpers.wishlistItems;
        String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
        return SizedBox(
          height: 250,
          width: ScreenSizing.width,
          child: Column(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Expanded(
                      child: ListView.separated(
                        itemBuilder: (context, index) {
                          String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                          String? productImage = "${context.read<ProductsCubit>().products?.data?.data?[index].images?.firstOrNull?.image}";
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
        );
      },
    );
  }
}

class SearchSuggested extends StatelessWidget {
  const SearchSuggested({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<SearchCubit, SearchState>(
      builder: (context, state) {
        bool isWishlistLoading = context.read<SearchCubit>().isWishlistLoading;
        int selectedProductId = context.read<SearchCubit>().selectedId;
        Map<int, bool> wishlistItems = Helpers.wishlistItems;
        String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
        return SizedBox(
          height: 250,
          width: ScreenSizing.width,
          child: Column(
            children: [
              Expanded(
                child: Row(
                  children: [
                    Expanded(
                      child: ListView.separated(
                        itemBuilder: (context, index) {
                          String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                          String? productImage = "${context.read<SearchCubit>().products?.data?.products?.data?[index].images?.firstOrNull?.image}";
                          String? productName = locale == 'ar' ? (context.read<SearchCubit>().products?.data?.products?.data?[index].nameArabic) : (context.read<SearchCubit>().products?.data?.products?.data?[index].nameGerman);
                          String? productCategory = locale == 'ar' ? (context.read<SearchCubit>().products?.data?.products?.data?[index].category?.nameArabic) : (context.read<SearchCubit>().products?.data?.products?.data?[index].category?.nameGerman);
                          String? productPrice = (context.read<SearchCubit>().products?.data?.products?.data?[index].minPrice);
                          int? productId = int.tryParse(context.read<SearchCubit>().products?.data?.products?.data?[index].id ?? '') ?? 0;
                          bool isFavorite = wishlistItems[productId] ?? false;
                          int? itemId = Helpers.wishlistProducts[productId];
                          return InkWell(
                            onTap: () {
                              BlocProvider.of<CartCubit>(context).resetCartBody();
                              BlocProvider.of<ProductsCubit>(context).getSingleProduct(id: productId ?? 0);
                              Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const ProductDetailsScreen(
                                      isSearch: true,
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
                                  await context.read<SearchCubit>().add2Wishlist(token: token, product_id: productId ?? 0);
                                }else{
                                  await context.read<SearchCubit>().deleteFromWishlist(token: token, product_id: productId ?? 0, itemId: itemId??0);
                                  await context.read<SearchCubit>().getWishlist(token: token);
                                }
                              },
                            ),
                          );
                        },
                        separatorBuilder: (context, index) => const SizedBox(
                          width: 10,
                        ),
                        itemCount: context.read<SearchCubit>().products?.data?.products?.data?.length ?? 0,
                        scrollDirection: Axis.horizontal,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class DescriptionSection extends StatelessWidget {
  const DescriptionSection({
    super.key,
    required this.locale,
    required this.product,
  });

  final String locale;
  final SingleProductModel? product;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          S.of(context).details,
          style: const TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.bold,
            color: Color(0xff4B5563),
          ),
        ),
        Row(
          children: [
            Expanded(
              child: Text(
                locale == 'ar' ? (product?.data?.descriptionArabic ?? '') : (product?.data?.descriptionGerman ?? ''),
                style: TextStyle(color: Colors.grey[600]),
              ),
            ),
          ],
        )
      ],
    );
  }
}

class StockRateWidget extends StatelessWidget {
  const StockRateWidget({super.key, required this.stock, required this.onTap, required this.rate});

  final String stock;
  final void Function()? onTap;
  final String rate;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SvgPicture.asset(Assets.appIconsStock),
        const SizedBox(
          width: 5,
        ),
        Expanded(
          child: Text(
            stock,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w400, color: Color(0xBF000000)),
          ),
        ),
        const SizedBox(
          width: 5,
        ),
        InkWell(
          onTap: onTap,
          child: Row(
            children: [
              Image.asset(
                Assets.appIconsRateIcon,
                width: 20,
                height: 20,
              ),
              const SizedBox(
                width: 5,
              ),
              Text(
                rate,
                style: TextStyle(fontWeight: FontWeight.w400, fontSize: 15, color: Constants.mainColor),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class PriceNameWidget extends StatelessWidget {
  const PriceNameWidget({
    super.key,
    required this.locale,
    required this.product,
    required this.minPrice,
  });

  final String locale;
  final SingleProductModel? product;
  final String? minPrice;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Text(
            locale == 'ar' ? (product?.data?.nameArabic ?? '') : (product?.data?.nameGerman ?? ''),
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        const SizedBox(
          width: 5,
        ),
        Row(
          children: [
            Text(
              minPrice ?? '',
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
            Text(
              ' EUR',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Constants.mainColor),
            ),
          ],
        ),
      ],
    );
  }
}

class ProductImageViewer extends StatelessWidget {
  const ProductImageViewer({
    super.key,
    this.images,
    this.imageUrl,
  });

  final List<SingleProductImages>? images;
  final String? imageUrl;

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.topCenter,
      children: [
        Container(
          height: 350,
          width: ScreenSizing.width,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(15),
          ),
        ),
        Container(
          height: 300,
          width: ScreenSizing.width,
          decoration: BoxDecoration(
            color: Colors.grey[300],
            borderRadius: BorderRadius.circular(15),
            boxShadow: [
              BoxShadow(
                color: Colors.grey[200]!,
                spreadRadius: 2,
                offset: const Offset(1, 2),
                blurRadius: 3,
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(15),
            child: InstaNetworkImageWidget(
              imageUrl: imageUrl?? images?.firstOrNull?.image ?? '',
              errorImage: Assets.imagesProductPlaceholder2,
            ),
          ),
        ),
        images == null || (images?.isEmpty ?? true)
            ? Container()
            : Positioned(
          bottom: 20,
          child: Center(
            child: Container(
              height: 65,
              constraints: BoxConstraints(maxWidth: ScreenSizing.width * 0.77, minWidth: 65),
              padding: const EdgeInsets.all(9),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
              ),
              child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemBuilder: (context, index) {
                    return Container(
                      height: 60,
                      width: 60,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(5),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(5),
                        child: InstaNetworkImageWidget(
                          imageUrl: images?[index].image ?? '',
                          errorImage: Assets.imagesProductPlaceholder2,
                        ),
                      ),
                    );
                  },
                  separatorBuilder: (context, index) => const SizedBox(
                    width: 5,
                  ),
                  itemCount: images?.length ?? 10),
            ),
          ),
        ),
      ],
    );
  }
}

class ProductDetailsLoadingWidget extends StatelessWidget {
  const ProductDetailsLoadingWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Shimmer.fromColors(
        highlightColor: Colors.white,
        baseColor: Colors.grey[300]!,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image placeholder
              Stack(
                alignment: Alignment.topCenter,
                children: [
                  Container(
                    height: 250,
                    width: ScreenSizing.width,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(15),
                    ),
                  ),
                  Container(
                    height: 200,
                    width: ScreenSizing.width,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(15),
                    ),
                  ),
                  Positioned(
                    bottom: 20,
                    child: Center(
                      child: Container(
                        height: 40,
                        width: ScreenSizing.width * 0.77,
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(15),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16.0),

              // Title placeholder
              Container(
                height: 20.0,
                width: double.infinity,
                color: Colors.grey,
              ),
              const SizedBox(height: 8.0),

              // Subtitle placeholder
              Container(
                height: 20.0,
                width: MediaQuery.of(context).size.width * 0.6,
                color: Colors.grey,
              ),
              const SizedBox(height: 16.0),

              // Description placeholder
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: List.generate(
                  4,
                      (index) => Padding(
                    padding: const EdgeInsets.only(bottom: 8.0),
                    child: Container(
                      height: 15.0,
                      width: double.infinity,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 16.0),

              // Price placeholder
              Container(
                height: 20.0,
                width: MediaQuery.of(context).size.width * 0.4,
                color: Colors.grey,
              ),

              const SizedBox(height: 16.0),

              // Button placeholder
              Container(
                height: 45.0,
                width: double.infinity,
                color: Colors.grey,
              ),
            ],
          ),
        ),
      ),
    );
  }
}