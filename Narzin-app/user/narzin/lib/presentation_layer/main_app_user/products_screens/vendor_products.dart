import 'package:auto_height_grid_view/auto_height_grid_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/model_layer/vendor_product_model.dart';
import 'package:narzin/presentation_layer/main_app_user/cart_screens/cart_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/products_screens/product_details_screen.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';

import '../../../generated/l10n.dart';

class VendorProducts extends StatelessWidget {
  const VendorProducts({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
        backgroundColor: Colors.white,
        leading: IconButton(
            onPressed: () {
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.arrow_back_ios_rounded)),
        title: Text(
          S.of(context).products,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            onPressed: () {
              Navigator.push(context, MaterialPageRoute(builder: (context) => const CartScreen(),));
              // Navigator.canPop(context) ? Navigator.pop(context) : null;
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
      body: BlocBuilder<ProductsCubit, ProductsState>(
        builder: (context, state) {
          bool isLoading = context.read<ProductsCubit>().isLoading;
          if (isLoading) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          }
          else {
            VendorProductsModel? products = context.read<ProductsCubit>().vendorProducts;
            String locale = BlocProvider.of<LocalizationCubit>(context).locale;
            if (products == null || (products.data?.isEmpty ?? true)) {
              return Center(
                child: Text(
                  'This Vendor Has no products',
                  style: TextStyle(fontSize: 17, color: Colors.grey[400]!),
                  textAlign: TextAlign.center,
                ),
              );
            }
            else{
              return SizedBox(
                height: ScreenSizing.height,
                width: ScreenSizing.width,
                child: AutoHeightGridView(
                  itemCount: products.data?.length ?? 0,
                  builder: (context, index) {
                    VendorProduct? product = products.data?[index];
                    String productName = locale == 'ar' ? (product?.nameArabic ?? '') : (product?.nameGerman ?? '');
                    String productPrice = (product?.minPrice ?? '');
                    String? productImage = "${product?.images?.firstOrNull?.image}";
                    String productCategory = locale == 'ar' ? (product?.category?.nameArabic ?? '') : (product?.category?.nameGerman ?? '');
                    int productId = (product?.id ?? 0);
                    // String rating = (product?.averageRating ?? 0).toString();
                    return InkWell(
                      onTap: () {
                        BlocProvider.of<ProductsCubit>(context).getSingleProduct(id: productId);
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const ProductDetailsScreen(
                              isSearch: null,
                            ),
                          ),
                        );
                      },
                      child: ProductItem(
                        productImage: productImage,
                        productName: productName,
                        category: productCategory,
                        priceFrom: productPrice,
                      ),
                    );
                  },
                ),
              );
            }

          }
        },
      ),
    );
  }
}
