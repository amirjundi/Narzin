import 'package:auto_height_grid_view/auto_height_grid_view.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/search_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/presentation_layer/main_app_user/cart_screens/cart_screen.dart';
import 'package:shimmer/shimmer.dart';

import '../../../generated/l10n.dart';
import '../home_screens/search_screens/searchSecond.dart';

class CategoriesScreen extends StatelessWidget {
  const CategoriesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).categories,
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
      body: Container(
        width: ScreenSizing.width,
        height: ScreenSizing.height,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        child: BlocBuilder<ProductsCubit, ProductsState>(
          builder: (context, state) {
            bool isLoading = context.read<ProductsCubit>().isLoading;
            return isLoading
                ? const LoadingGridBuilder()
                : AutoHeightGridView(
                    padding: EdgeInsets.zero,
                    itemCount: context.read<ProductsCubit>().categories?.data?.length ?? 0,
                    builder: (context, index) {
                      String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                      String? categoryImage = "${context.read<ProductsCubit>().categories?.data?[index].image}";
                      String? categoryName = locale == 'ar' ? (context.read<ProductsCubit>().categories?.data?[index].nameArabic) : (context.read<ProductsCubit>().categories?.data?[index].nameGerman);
                      return InkWell(
                        onTap: () {
                          BlocProvider.of<SearchCubit>(context).resetFilters();
                          BlocProvider.of<SearchCubit>(context).selectedCategory = (context.read<ProductsCubit>().categories?.data?[index].id??0).toString();
                          // BlocProvider.of<SearchCubit>(context).prepareFilteredSearchUrl();
                          BlocProvider.of<SearchCubit>(context).getSearchedProducts();
                          Navigator.push(context, MaterialPageRoute(builder: (context) => const SearchSecond(),),);
                        },
                        child: Container(
                          margin: const EdgeInsets.symmetric(vertical: 10),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                height: 80,
                                width: 80,
                                decoration: BoxDecoration(
                                  color: const Color(0xffE5E7EB),
                                  borderRadius: BorderRadius.circular(20),
                                  image: DecorationImage(image: NetworkImage(categoryImage,),fit: BoxFit.cover)
                                ),
                              ),
                              const SizedBox(
                                height: 5,
                              ),
                              Text(
                                categoryName ?? '',
                                style: const TextStyle(fontSize: 15, color: Color(0xff4B5563)),
                                textAlign: TextAlign.center,
                              )
                            ],
                          ),
                        ),
                      );
                    },
                    crossAxisCount: 3,
                  );
          },
        ),
      ),
    );
  }
}

class LoadingGridBuilder extends StatelessWidget {
  const LoadingGridBuilder({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.white,
      child: AutoHeightGridView(
        padding: EdgeInsets.zero,
        itemCount: 10,
        builder: (context, index) {
          return Container(
            margin: const EdgeInsets.symmetric(vertical: 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  height: 80,
                  width: 80,
                  decoration: BoxDecoration(color: const Color(0xffE5E7EB), borderRadius: BorderRadius.circular(20)),
                ),
                const SizedBox(
                  height: 5,
                ),
                const Text(
                  'data',
                  style: TextStyle(fontSize: 17, color: Color(0xff4B5563)),
                )
              ],
            ),
          );
        },
        crossAxisCount: 3,
      ),
    );
  }
}
