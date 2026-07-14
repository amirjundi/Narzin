import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/search_cubit.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/model_layer/single_product_model.dart';
import 'package:narzin/model_layer/vendor_data_model.dart';
import 'package:narzin/presentation_layer/main_app_user/cart_screens/cart_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/products_screens/reviews_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/products_screens/vendor_products.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/size_guide_widget.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/constants.dart';
import '../../../generated/l10n.dart';
import '../../../widgets/app_infrastructure_widgets/product_details_widgets.dart';
import '../../../widgets/variants_widgets/variants_widgets.dart';

class ProductDetailsScreen extends StatelessWidget {
  const ProductDetailsScreen({super.key, this.minPrice, this.maxPrice, required this.isSearch});

  final bool? isSearch;

  final String? minPrice;
  final String? maxPrice;

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        BlocProvider.of<CartCubit>(context).resetCartBody();
        BlocProvider.of<ProductsCubit>(context).resetVariantAttrs();
        BlocProvider.of<ProductsCubit>(context).imageToShow = null;
        return true;
      },
      child: Scaffold(
        appBar: AppBar(
          toolbarHeight: kToolbarHeight * 1.3,
          bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
          backgroundColor: Colors.white,
          leading: IconButton(
            onPressed: () {
              BlocProvider.of<CartCubit>(context).resetCartBody();
              BlocProvider.of<ProductsCubit>(context).resetVariantAttrs();
              BlocProvider.of<ProductsCubit>(context).imageToShow = null;
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.arrow_back_ios_rounded),
          ),
          title: SizedBox(
            width: ScreenSizing.width * 0.5,
            child: SvgPicture.asset(
              Assets.appIconsInappLogo,
              fit: BoxFit.fitWidth,
            ),
          ),
          automaticallyImplyLeading: false,
          actions: [
            IconButton(
              onPressed: () {
                Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const CartScreen(),
                    ));
                // Navigator.canPop(context) ? Navigator.pop(context) : null;
              },
              icon: Stack(
                children: [
                  const SizedBox(
                    height: 60,
                    width: 40,
                  ),
                  Positioned(
                      top: 0,
                      left: 0,
                      child: Icon(
                        Icons.shopping_cart,
                        color: Constants.mainColor,
                        size: 25,
                      )),
                  Positioned(
                    top: 0,
                    right: 0,
                    child: BlocBuilder<CartCubit, CartState>(
                      builder: (context, state) {
                        return CircleAvatar(
                          radius: 9,
                          backgroundColor: Colors.red,
                          child: Text(
                            (context.read<CartCubit>().myCart?.data?.length ?? 0).toString(),
                            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                          ),
                        );
                      },
                    ),
                  )
                ],
              ),
            ),
          ],
          centerTitle: true,
        ),
        body: BlocConsumer<ProductsCubit, ProductsState>(
          listener: (context, state) {
            if (state is SingleProductSuccess) {
              context.read<ProductsCubit>().createStock(context);
              context.read<ProductsCubit>().generateVariantColor(BlocProvider.of<LocalizationCubit>(context).locale);
              context.read<ProductsCubit>().getVendorDetails(vendor_id: context.read<ProductsCubit>().singleProduct?.data?.vendorId ?? 0);
            }
          },
          builder: (context, state) {
            bool isLoading = context.read<ProductsCubit>().isLoading;
            bool isLoading2 = context.read<ProductsCubit>().isLoading2;
            String stock = context.read<ProductsCubit>().stock;
            print(stock);
            String locale = BlocProvider.of<LocalizationCubit>(context).locale;
            if (isLoading) {
              return const ProductDetailsLoadingWidget();
            } else {
              SingleProductModel? product = context.read<ProductsCubit>().singleProduct;
              VendorDataModel? vendor = context.read<ProductsCubit>().vendor;
              if (product != null) {
                return SizedBox(
                  height: ScreenSizing.height,
                  width: ScreenSizing.width,
                  child: SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 10),
                          child: Column(
                            children: [
                              ProductImageViewer(
                                images: product.data?.images,
                                imageUrl:context.read<ProductsCubit>().imageToShow ?? product.data?.images?.firstOrNull?.image ?? '',
                              ),
                              const SizedBox(
                                height: 5,
                              ),
                              PriceNameWidget(locale: locale, product: product, minPrice: minPrice),
                              const SizedBox(
                                height: 10,
                              ),
                              StockRateWidget(
                                rate: (product.data?.averageRating).toString(),
                                stock: stock,
                                onTap: () {
                                  context.read<ProductsCubit>().getProductReviews(
                                        token: BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '',
                                        productId: BlocProvider.of<ProductsCubit>(context).singleProduct?.data?.id,
                                      );
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => ReviewsScreen(),
                                    ),
                                  );
                                },
                              ),
                              const SizedBox(
                                height: 30,
                              ),
                              DescriptionSection(locale: locale, product: product),
                              const SizedBox(
                                height: 30,
                              ),
                              SizeGuideWidget(sizeChart: product.data?.sizeChart),
                              // ===== Colors Section =====
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Text(
                                    S.of(context).color,
                                    style: const TextStyle(
                                      fontSize: 17,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xff4B5563),
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  SizedBox(
                                    height: 40,
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: ListView.separated(
                                            scrollDirection: Axis.horizontal,
                                            itemBuilder: (context, index) {
                                              final cubit = context.read<ProductsCubit>();

                                              // اللون (المفتاح)
                                              final entry = cubit.colorGroups.entries.elementAt(index);
                                              final colorValue = entry.key;

                                              // pattern المرتبط باللون
                                              final patternUrl = cubit.colorPatterns[colorValue];

                                              // قيمة العرض (لو فيه pattern نعرضه، غير كده نعرض اللون)
                                              final displayValue = patternUrl ?? colorValue;

                                              return InkWell(
                                                onTap: () {
                                                  cubit.resetOtherAttrs();
                                                  cubit.setSelectedColor(index,locale);
                                                  cubit.setImageToShow();
                                                  // variantId بيتحدد عن طريق resolveSelectedVariantId بعد اختيار باقي الخصائص
                                                  cubit.generateVariantAttrs(locale);
                                                },
                                                child: index == cubit.selectedColorIndex
                                                    ? SelectedColorWidget(colorsAttrs: displayValue, index: index)
                                                    : UnselectedColorWidget(colorsAttrs: displayValue, index: index),
                                              );
                                            },
                                            separatorBuilder: (context, index) => const SizedBox(width: 5),
                                            itemCount: context.read<ProductsCubit>().colorGroups.entries.length,
                                          ),
                                        ),
                                      ],
                                    ),
                                  )
                                ],
                              ),

                              const SizedBox(height: 30),

// ===== Other Attributes Section =====
                              ...context.read<ProductsCubit>().otherAttributes.entries.map((entry) {
                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: [
                                    Text(
                                      entry.key,
                                      style: const TextStyle(
                                        fontSize: 17,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xff4B5563),
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    SizedBox(
                                      height: 55,
                                      child: Row(
                                        children: [
                                          Expanded(
                                            child: ListView.separated(
                                              scrollDirection: Axis.horizontal,
                                              itemBuilder: (context, index) {
                                                final attrValue = entry.value[index];
                                                final isSelected = context.read<ProductsCubit>().selectedAttributes[entry.key] == index;

                                                return InkWell(
                                                  onTap: () {
                                                    context.read<ProductsCubit>().setSelectedAttr(entry.key, index,locale);
                                                  },
                                                  child: isSelected
                                                      ? SelectedSizeWidget(value: attrValue)
                                                      : UnselectedSizeWidget(value: attrValue),
                                                );
                                              },
                                              separatorBuilder: (context, index) => const SizedBox(width: 10),
                                              itemCount: entry.value.length,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(height: 20),
                                  ],
                                );
                              }),

                              const SizedBox(
                                height: 30,
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10),
                          height: 70,
                          child: Row(
                            children: [
                              BlocBuilder<CartCubit, CartState>(
                                builder: (cartContext, state) {
                                  int quantity = cartContext.read<CartCubit>().quantity;
                                  return SizedBox(
                                    width: 80,
                                    child: CustomSignIn_UpThree(
                                      padding: EdgeInsets.zero,
                                      customizeChild: Column(
                                        crossAxisAlignment: CrossAxisAlignment.center,
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            S.of(context).quantity,
                                            style: TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.w800,
                                              color: Constants.mainColor,
                                            ),
                                          ),
                                          Text(
                                            '$quantity',
                                            style: TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.w900,
                                              color: Constants.mainColor,
                                            ),
                                          ),
                                        ],
                                      ),
                                      title: 'qty',
                                      ontap: () {
                                        cartContext.read<CartCubit>().resetCartBody();
                                        buildShowModalBottomSheet(context);
                                      },
                                    ),
                                  );
                                },
                              ),
                              const SizedBox(
                                width: 5,
                              ),
                              Expanded(
                                flex: 3,
                                child: BlocBuilder<CartCubit, CartState>(
                                  builder: (cartContext, state) {
                                    bool isLoading = cartContext.read<CartCubit>().isLoading;

                                    return CustomSignIn_UpOne(
                                      customizeChild: isLoading
                                          ? const Center(
                                              child: CircularProgressIndicator(
                                                color: Colors.white,
                                              ),
                                            )
                                          : Center(
                                              child: Row(
                                                mainAxisAlignment: MainAxisAlignment.center,
                                                crossAxisAlignment: CrossAxisAlignment.center,
                                                children: [
                                                  const Spacer(),
                                                  Icon(
                                                    Icons.add_shopping_cart_outlined,
                                                    color: context.read<ProductsCubit>().isOutOfStock ? Colors.black54 : Colors.white,
                                                  ),
                                                  const SizedBox(
                                                    width: 5,
                                                  ),
                                                  Expanded(
                                                    flex: 15,
                                                    child: Text(
                                                      context.read<ProductsCubit>().isOutOfStock ? S.of(context).out_of_stock : S.of(context).add_to_cart,
                                                      style: TextStyle(
                                                        fontSize: 16,
                                                        fontWeight: FontWeight.w700,
                                                        color: context.read<ProductsCubit>().isOutOfStock ? Colors.black54 : Colors.white,
                                                      ),
                                                      textAlign: TextAlign.center,
                                                    ),
                                                  ),
                                                  const Spacer(),
                                                ],
                                              ),
                                            ),
                                      title: S.of(context).add_to_cart,
                                      ontap: context.read<ProductsCubit>().isOutOfStock
                                          ? null
                                          : () async {
                                              int selectedColor = context.read<ProductsCubit>().selectedColorIndex;
                                              String variantId = context.read<ProductsCubit>().selectedVariantId;
                                              if (variantId.isEmpty) {
                                                Helpers.showColoredToast(message: 'Please Select all attributes for a variant!', color: Colors.red);
                                                return;
                                              }
                                              if (selectedColor != -1) {
                                                final variants = context.read<ProductsCubit>().singleProduct?.data?.variants;
                                                final selectedVariantPrice = variants
                                                    ?.firstWhere(
                                                      (element) => element.id.toString() == variantId,
                                                      orElse: () => SingleProductVariants(),
                                                    )
                                                    .price;
                                                cartContext.read<CartCubit>().formulateCartBody(
                                                      variantId,
                                                      int.tryParse(context.read<ProductsCubit>().singleProduct?.data?.id ?? "0") ?? 0,
                                                      variantPrice: selectedVariantPrice,
                                                    );
                                                await cartContext.read<CartCubit>().addToCart(token: BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '');
                                                await cartContext.read<CartCubit>().getMyCart(token: BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '');
                                              } else {
                                                Helpers.showColoredToast(message: 'Please Select all attributes for a variant!', color: Colors.red);
                                              }
                                            },
                                    );
                                  },
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(
                          height: 20,
                        ),
                        isSearch == null
                            ? Container()
                            : Container(
                                height: 120,
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                                color: Colors.grey[200],
                                child: isLoading2
                                    ? const VendorLoadingWidget()
                                    : InkWell(
                                        child: VendorWidget(
                                          vendor: vendor,
                                          locale: locale,
                                        ),
                                        onTap: () {
                                          context.read<ProductsCubit>().getVendorProducts(vendor_id: vendor?.data?.id);
                                          Navigator.pushReplacement(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) => const VendorProducts(),
                                            ),
                                          );
                                        },
                                      ),
                              ),
                        const SizedBox(
                          height: 30,
                        ),
                        isSearch == null
                            ? Container()
                            : Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 10),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          S.of(context).suggested,
                                          style: TextStyle(fontSize: 19, fontWeight: FontWeight.w600, color: Colors.grey[600]!),
                                        )
                                      ],
                                    ),
                                    const SizedBox(
                                      height: 10,
                                    ),
                                    isSearch != null ? (isSearch! ? const SearchSuggested() : const ProductsSuggested()) : Container(),
                                  ],
                                ),
                              ),
                        const SizedBox(
                          height: 30,
                        ),
                      ],
                    ),
                  ),
                );
              } else {
                return const Center(
                  child: Text(
                    'There is a problem come back later',
                    style: TextStyle(fontSize: 17, color: Colors.red),
                  ),
                );
              }
            }
          },
        ),
      ),
    );
  }

  Future<dynamic> buildShowModalBottomSheet(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      builder: (context) {
        return BlocBuilder<CartCubit, CartState>(
          builder: (cartContext, state) {
            return Container(
              height: 300,
              width: ScreenSizing.width,
              padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 20),
              child: Column(
                children: [
                  Expanded(
                    child: SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                S.of(context).order_initialization,
                                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w500),
                              ),
                              IconButton(
                                onPressed: () {
                                  Navigator.canPop(context) ? Navigator.pop(context) : null;
                                },
                                icon: const Icon(Icons.close),
                              ),
                            ],
                          ),
                          const SizedBox(
                            height: 20,
                          ),
                          const SizedBox(
                            height: 10,
                          ),
                          CustomTextFormField(
                            title: S.of(context).quantity,
                            hint: S.of(context).quantity_placeholder,
                            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                            keyboardType: const TextInputType.numberWithOptions(),
                            onChanged: (value) {
                              cartContext.read<CartCubit>().addQuantity(int.tryParse(value) ?? 1);
                            },
                          ),
                        ],
                      ),
                    ),
                  ),
                  CustomSignIn_UpThree(
                    title: S.of(context).confirm,
                    ontap: () {
                      Navigator.canPop(context) ? Navigator.pop(context) : null;
                    },
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}




