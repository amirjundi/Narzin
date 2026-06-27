import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/wallet_cubits/wallet_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/my_cart_model.dart';
import 'package:narzin/presentation_layer/main_app_user/orderScreen/place_order_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/products_screens/product_details_screen.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:shimmer/shimmer.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  @override
  void initState() {
    // TODO: implement initState
    String? token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token;
    if (token != null) {
      BlocProvider.of<CartCubit>(context).getMyCart(token: token);
    }

    super.initState();
  }

  late var localizedDate;

  @override
  Widget build(BuildContext context) {
    localizedDate = DateFormat('d MMMM yyyy', BlocProvider.of<LocalizationCubit>(context).locale);
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).cart,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: true,
        actions: [
          IconButton(
            onPressed: () {
              String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
              showMenu(
                context: context,
                position: RelativeRect.fromLTRB(0, kToolbarHeight * 1.33, ScreenSizing.width, 0), // Position of the dropdown
                items: [
                  PopupMenuItem(
                    child: Text(S.of(context).clear_cart),
                    onTap: () async {
                      var res = await BlocProvider.of<CartCubit>(context).clearMyCart(token: token);
                      if (res == null) {
                        await BlocProvider.of<CartCubit>(context).getMyCart(token: token);
                      }
                    },
                  ),
                ],
              );
              // Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.more_vert_sharp),
          ),
        ],
        centerTitle: true,
      ),
      body: BlocBuilder<CartCubit, CartState>(
        builder: (context, state) {
          String locale = BlocProvider.of<LocalizationCubit>(context).locale;
          MyCartModel? myCart = context.read<CartCubit>().myCart;
          bool isLoading = context.read<CartCubit>().isLoading;
          bool isLoading2 = context.read<CartCubit>().isLoadingQuantity;
          bool isLoadingDelete = context.read<CartCubit>().isLoadingDeleteItem;
          if (isLoading) {
            return const Center(
              child: CircularProgressIndicator(),
            );
          } else {
            var cartData = myCart?.data;
            Map<String, int> quantities = context.read<CartCubit>().cartQuantities;
            if (cartData == null || cartData.isEmpty) {
              return const EmptyCartWidget();
            } else {
              String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
              return Container(
                height: ScreenSizing.height,
                width: ScreenSizing.width,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Expanded(
                      child: SizedBox(
                        // height: ScreenSizing.height * 0.35,
                        child: ListView.separated(
                            shrinkWrap: true,
                            // physics: const NeverScrollableScrollPhysics(),
                            itemBuilder: (context, index) {
                              return isLoading2
                                  ? Shimmer.fromColors(
                                      highlightColor: Colors.white,
                                      baseColor: Colors.grey[300]!,
                                      child: CartItem(
                                        locale: locale,
                                        priceFrom: cartData[index].productVariant?.price,
                                        productName: locale == 'ar' ? (cartData[index].product?.nameArabic ?? '') : (cartData[index].product?.nameGerman ?? ''),
                                        quantity: quantities['${cartData[index].id ?? 0}'].toString(),
                                        onIncrease: () async {},
                                        onDecrease: () async {},
                                        onDelete: () {},
                                      ),
                                    )
                                  : InkWell(
                                      onTap: () {
                                        BlocProvider.of<ProductsCubit>(context).getSingleProduct(id: int.tryParse(cartData[index].productId.toString()) ?? 0);
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (context) => const ProductDetailsScreen(
                                              isSearch: null,
                                            ),
                                          ),
                                        );
                                      },
                                      child: CartItem(
                                        isOutOfStock: cartData[index].outOfStock,
                                        icon: Icons.favorite_border,
                                        locale: locale,
                                        productImage: cartData[index].product?.images?.firstOrNull?.image ?? '',
                                        onIconPressed: () {},
                                        priceFrom: cartData[index].productVariant?.price,
                                        productName: locale == 'ar' ? (cartData[index].product?.nameArabic ?? '') : (cartData[index].product?.nameGerman ?? ''),
                                        quantity: quantities['${cartData[index].id ?? 0}'].toString(),
                                        onIncrease: () async {
                                          context.read<CartCubit>().setQuantity((quantities['${cartData[index].id ?? 0}'] ?? 0) + 1, cartData[index].id.toString());
                                          var res = await context.read<CartCubit>().updateCartItemQuantity(
                                                token: token,
                                                itemID: int.tryParse(cartData[index].id.toString()),
                                              );
                                          if (res != null) {
                                            context.read<CartCubit>().setQuantity((quantities['${cartData[index].id ?? 0}'] ?? 0) - 1, cartData[index].id.toString());
                                          }

                                          context.read<CartCubit>().getCartTotal();
                                          // context.read<CartCubit>().getMyCart(token: token,);
                                        },
                                        onDecrease: () async {
                                          context.read<CartCubit>().setQuantity((quantities['${cartData[index].id ?? 0}'] ?? 0) - 1, cartData[index].id.toString());
                                          var res = await context.read<CartCubit>().updateCartItemQuantity(
                                                token: token,
                                                itemID: int.tryParse(cartData[index].id.toString()),
                                              );
                                          if (res != null) {
                                            context.read<CartCubit>().setQuantity((quantities['${cartData[index].id ?? 0}'] ?? 0) + 1, cartData[index].id.toString());
                                          }
                                          context.read<CartCubit>().getCartTotal();
                                        },
                                        deleteIcon: isLoadingDelete
                                            ? const SizedBox(
                                                width: 10,
                                                height: 10,
                                                child: CircularProgressIndicator(
                                                  color: Colors.red,
                                                ),
                                              )
                                            : null,
                                        onDelete: () async {
                                          await context.read<CartCubit>().deleteCartItem(token: token, itemID: int.tryParse(cartData[index].id.toString()) ?? 0);
                                          context.read<CartCubit>().getMyCart(token: token);
                                        },
                                      ),
                                    );
                            },
                            separatorBuilder: (context, index) => const SizedBox(
                                  height: 10,
                                ),
                            itemCount: cartData.length),
                      ),
                    ),
                    const SizedBox(
                      height: 10,
                    ),
                    const Divider(
                      height: 1,
                      color: Colors.grey,
                      thickness: 1,
                      endIndent: 0,
                      indent: 0,
                    ),
                    const SizedBox(
                      height: 20,
                    ),
                    Text(
                      S.of(context).order_summary,
                      style: const TextStyle(color: Color(0xff4B5563), fontSize: 18, fontWeight: FontWeight.w500),
                    ),
                    Card(
                      color: Constants.lightSecondaryColor,
                      child: Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  "${S.of(context).subtotal} (${cartData.length})",
                                  style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                ),
                                Text(
                                  "${context.read<CartCubit>().totalPrice} EUR",
                                  style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                ),
                              ],
                            ),
                            const SizedBox(
                              height: 10,
                            ),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  S.of(context).tax,
                                  style: const TextStyle(color: Color(0xff000000), fontSize: 16, fontWeight: FontWeight.w500),
                                ),
                                Spacer(),
                                Expanded(
                                  child: Text(
                                    S.of(context).TaxDetail,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(color: Colors.orange, fontSize: 14, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(
                              height: 20,
                            ),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  S.of(context).total,
                                  style: const TextStyle(color: Color(0xff000000), fontSize: 16, fontWeight: FontWeight.w500),
                                ),
                                Text(
                                  "${context.read<CartCubit>().totalPrice} EUR",
                                  style: const TextStyle(color: Color(0xff000000), fontSize: 16, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(
                      height: 10,
                    ),
                    // Row(
                    //   mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    //   children: [
                    //     Text(
                    //       S.of(context).estimated_delivery_date,
                    //       style: const TextStyle(color: Color(0xff000000), fontSize: 15, fontWeight: FontWeight.w400),
                    //     ),
                    //     Text(
                    //       "${localizedDate.format(DateTime.now())}",
                    //       style: const TextStyle(color: Color(0xff000000), fontSize: 15, fontWeight: FontWeight.w400),
                    //     ),
                    //   ],
                    // ),
                  ],
                ),
              );
            }
          }
        },
      ),
      bottomNavigationBar: BlocBuilder<CartCubit, CartState>(
        builder: (context, state) {
          MyCartModel? myCart = context.read<CartCubit>().myCart;
          var cartData = myCart?.data;
          return (cartData == null || cartData.isEmpty)
              ? Container(
                  height: 0,
                )
              : Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20.0),
                  child: SizedBox(
                    height: 60,
                    child: BlocBuilder<OrderCubit, OrderState>(
                      builder: (orderContext, state) {
                        bool isLoading = context.read<OrderCubit>().isLoading;
                        return CustomSignIn_UpOne(
                          title: S.of(context).checkout,
                          customizeChild: isLoading
                              ? const CircularProgressIndicator(
                                  color: Colors.white,
                                )
                              : Text(
                                  S.of(context).checkout,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                ),
                          ontap: isLoading
                              ? null
                              : () async {
                                  String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                                  orderContext.read<OrderCubit>().resetEveryThing();
                                  await orderContext.read<OrderCubit>().getDeliveryZones(token: token);
                                  context.read<CartCubit>().getCartTotal();
                                  await BlocProvider.of<WalletCubit>(context).getWallet(token: token);
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => const PlaceOrderScreen(),
                                    ),
                                  );
                                },
                        );
                      },
                    ),
                  ),
                );
        },
      ),
    );
  }
}

class EmptyCartWidget extends StatelessWidget {
  const EmptyCartWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: ScreenSizing.height,
      width: ScreenSizing.width,
      padding: const EdgeInsets.symmetric(horizontal: 10),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              height: ScreenSizing.height * 0.1,
            ),
            SizedBox(
              height: ScreenSizing.height * 0.25,
              width: ScreenSizing.width,
              child: Image.asset(Assets.imagesEmptyCart),
            ),
            const SizedBox(
              height: 20,
            ),
            Text(
              S.of(context).empty_cart,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: Color(0xff4B5563)),
              textAlign: TextAlign.center,
            ),
            Text(
              S.of(context).home_page_message,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w400, color: Color(0xff4B5563)),
              textAlign: TextAlign.center,
            ),
            const SizedBox(
              height: 20,
            ),
            BlocBuilder<MainHubCubit, MainHubState>(
              builder: (hubContext, state) {
                return Container(
                  height: 65,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: CustomSignIn_UpOne(
                    title: S.of(context).go_to_home,
                    ontap: () {
                      hubContext.read<MainHubCubit>().setCurrentIndex(0);
                    },
                  ),
                );
              },
            )
          ],
        ),
      ),
    );
  }
}
