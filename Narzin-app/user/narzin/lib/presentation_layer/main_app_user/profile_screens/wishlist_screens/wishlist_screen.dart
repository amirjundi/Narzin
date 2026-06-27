import 'package:auto_height_grid_view/auto_height_grid_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/model_layer/wishlist_model.dart';
import 'package:narzin/presentation_layer/main_app_user/cart_screens/cart_screen.dart';
import 'package:shimmer/shimmer.dart';

import '../../../../bussiness_logic/localization_cubit/localization_cubit.dart';
import '../../../../bussiness_logic/login_cubits/login_cubit.dart';
import '../../../../core/helpers.dart';
import '../../../../generated/l10n.dart';
import '../../../../widgets/app_infrastructure_widgets/product_item_widget.dart';
import '../../products_screens/product_details_screen.dart';

class WishlistScreen extends StatelessWidget {
  const WishlistScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).favorites,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        actions: [
          IconButton(
            onPressed: () {
              // Navigator.canPop(context) ? Navigator.pop(context) : null;
              Navigator.push(context, MaterialPageRoute(builder: (context) => const CartScreen(),));
            },
            icon: Stack(
              children: [
                const SizedBox(height: 60,width: 40,),
                Positioned(top: 0,left: 0,child: Icon(Icons.shopping_cart,color: Constants.mainColor,size: 25,)),
                Positioned(top: 0,right: 0,child: BlocBuilder<CartCubit, CartState>(
                  builder: (context, state) {
                    return CircleAvatar(radius: 9,backgroundColor: Colors.red,child: Text((context.read<CartCubit>().myCart?.data?.length ?? 0).toString(),style: const TextStyle(color: Colors.white,fontSize: 13,fontWeight: FontWeight.bold),),);
                  },
                ))
              ],
            ),
          ),
        ],
        centerTitle: true,
      ),
      body: Container(
        height: ScreenSizing.height,
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        child: BlocConsumer<ProductsCubit, ProductsState>(
          builder: (context, state) {
            bool isLoading = context.read<ProductsCubit>().isLoading;
            String locale = BlocProvider.of<LocalizationCubit>(context).locale;
            int selectedProductId = context.read<ProductsCubit>().selectedId;
            Map<int, bool> wishlistItems = Helpers.wishlistItems;
            String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
            if (isLoading) {
              return const LoadingWidget();
            } else {
              WishlistModel? wishlist = context.read<ProductsCubit>().wishlist;
              List<WishlistItemData>? wishItems = wishlist?.data;
              int length = wishItems?.length ?? 0;
              if (length == 0 || wishItems == null) {
                return Center(
                  child: Text(S.of(context).your_wishlist_is_empty,style: TextStyle(color: Colors.grey[500]!,fontWeight: FontWeight.w600,fontSize: 18),),
                );
              }
              return AutoHeightGridView(
                padding: EdgeInsets.zero,
                itemCount: length,
                builder: (context, index) {
                  var product = wishItems[index].product;
                  String? productImage = product?.images?.firstOrNull?.url??'';
                  String? productName = locale == 'ar' ? (product?.nameArabic) : (product?.nameGerman);
                  int productId = (wishItems[index].productId ?? 0);
                  bool isFavorite = wishlistItems[productId] ?? false;
                  String rating = product?.averageRating.toString() ?? '0.0';
                  print(length);
                  return ProductItem(
                    productImage: productImage,
                    productName: productName,
                    category: '',
                    priceFrom: null,
                    rating: rating.length > 3? rating.substring(0, 3):rating,
                    icon: isFavorite ? Icons.favorite : Icons.favorite_border,
                    IconWidget: selectedProductId == product?.id
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
                      } else {
                        await context.read<ProductsCubit>().deleteFromWishlist(token: token, itemId: wishItems[index].id ?? 0, product_id: productId);
                        await context.read<ProductsCubit>().getWishlist(token: token);
                      }
                    },
                    onTap: () {
                      context.read<ProductsCubit>().getSingleProduct(id: productId ?? 0);
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const ProductDetailsScreen(
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
      child: AutoHeightGridView(
        itemCount: 8,
        builder: (context, index) => ProductItem(
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
      ),
    );
  }
}
