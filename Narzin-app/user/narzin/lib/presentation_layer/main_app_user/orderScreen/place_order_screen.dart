import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/bussiness_logic/wallet_cubits/wallet_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/my_cart_model.dart';
import 'package:narzin/presentation_layer/main_app_user/address_screens/add_address_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/orderScreen/order_success_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/orderScreen/payment_page.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bussiness_logic/profile_cubits/profile_cubit.dart';
import '../../../model_layer/addresses_model.dart';
import '../../../widgets/tools_widgets/custom_radio_button.dart';

class PlaceOrderScreen extends StatefulWidget {
  const PlaceOrderScreen({super.key});

  @override
  State<PlaceOrderScreen> createState() => _PlaceOrderScreenState();
}

class _PlaceOrderScreenState extends State<PlaceOrderScreen> {
  late String locale;
  late String token;
  int shippmentIndex = -1;
  GlobalKey key = GlobalKey();

  @override
  void initState() {
    // TODO: implement initState
    token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
    locale = BlocProvider.of<LocalizationCubit>(context).locale;
    BlocProvider.of<OrderCubit>(context).getDeliveryZones(token: token);
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () {
        BlocProvider.of<CartCubit>(context).discountedItems.clear();
        return Future.value(true);
      },
      child: Scaffold(
        appBar: AppBar(
          toolbarHeight: kToolbarHeight * 1.1,
          bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
          backgroundColor: Colors.white,
          leading: IconButton(
            onPressed: () {
              BlocProvider.of<CartCubit>(context).discountedItems.clear();
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.arrow_back_ios_rounded),
          ),
          title: Text(
            S.of(context).complete_order,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          automaticallyImplyLeading: false,
          actions: [
            IconButton(
              onPressed: () {
                // Navigator.canPop(context) ? Navigator.pop(context) : null;
              },
              icon: const Icon(Icons.more_vert_sharp),
            ),
          ],
          centerTitle: true,
        ),
        body: Container(
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Container(
                //   height: 160,
                //   width: ScreenSizing.width,
                //   decoration: BoxDecoration(
                //     borderRadius: BorderRadius.circular(10),
                //     border: Border.all(color: Colors.grey[300]!),
                //   ),
                //   child: ClipRRect(
                //     borderRadius: BorderRadius.circular(10),
                //     child: const OrdersMapViewer(),
                //   ),
                // ),
                const SizedBox(
                  height: 10,
                ),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: BlocBuilder<ProfileCubit, ProfileState>(
                    builder: (context, state) {
                      List<AddressData> addresses = context.read<ProfileCubit>().addressesModel?.data ?? [];
                      bool isLoading = context.read<ProfileCubit>().isLoading;
                      return addresses.isEmpty
                          ? const NoAddressesWidget()
                          : SingleChildScrollView(
                              child: Container(
                                constraints: const BoxConstraints(minHeight: 100, maxHeight: 200),
                                child: ListView.separated(
                                    shrinkWrap: true,
                                    itemBuilder: (context, index) {
                                      return isLoading
                                          ? Shimmer.fromColors(
                                              baseColor: Colors.grey[300]!,
                                              highlightColor: Colors.white,
                                              child: AddressItem(
                                                value: addresses[index].id.toString(),
                                                address: addresses[index].address,
                                                selectedAddress: context.read<ProfileCubit>().selectedAddress,
                                                onDelete: () {},
                                                onChanged: (value) {},
                                              ),
                                            )
                                          : InkWell(
                                              onTap: () {
                                                // BlocProvider.of<OrderCubit>(context).captureNewPosition(
                                                //   LatLng(
                                                //     double.tryParse(addresses[index].latitude ?? '0') ?? 0,
                                                //     double.tryParse(addresses[index].longitude ?? '0') ?? 0,
                                                //   ),
                                                // );
                                                context.read<ProfileCubit>().setSelectedGroup(
                                                      addresses[index].id.toString() ?? '',
                                                      addresses[index].title ?? '',
                                                    );
                                              },
                                              child: AddressItem(
                                                value: addresses[index].id.toString(),
                                                address: context.read<ProfileCubit>().localizeAddress(addresses[index].address ?? ''),
                                                selectedAddress: context.read<ProfileCubit>().selectedAddress,
                                                onDelete: () async {
                                                  await context.read<ProfileCubit>().deleteAddress(token: token, address_id: addresses[index].id.toString());
                                                  context.read<ProfileCubit>().getAddresses(token: token);
                                                  // BlocProvider.of<OrderCubit>(context).getCoordinates();
                                                },
                                                onChanged: (value) {
                                                  // BlocProvider.of<OrderCubit>(context).captureNewPosition(
                                                  //   LatLng(
                                                  //     double.tryParse(addresses[index].latitude ?? '0') ?? 0,
                                                  //     double.tryParse(addresses[index].longitude ?? '0') ?? 0,
                                                  //   ),
                                                  // );
                                                  context.read<ProfileCubit>().setSelectedGroup(value ?? '', addresses[index].title ?? '');
                                                },
                                              ),
                                            );
                                    },
                                    separatorBuilder: (context, index) => const SizedBox(
                                          height: 10,
                                        ),
                                    itemCount: addresses.length),
                              ),
                            );
                    },
                  ),
                ),
                const SizedBox(
                  height: 10,
                ),
                BlocBuilder<ProfileCubit, ProfileState>(
                  builder: (context, state) {
                    bool isLoading = context.read<ProfileCubit>().isLoading;
                    return InkWell(
                      borderRadius: BorderRadius.circular(10),
                      onTap: isLoading
                          ? null
                          : () async {
                              context.read<ProfileCubit>().resetEveryThing();
                              // await context.read<ProfileCubit>().getCoordinates();
                              var res = await context.read<ProfileCubit>().getDeliveryZones(token: token);
                              if (res == null) {
                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const AddAddressScreen(),
                                  ),
                                );
                              }
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
                    );
                  },
                ),
                const SizedBox(
                  height: 10,
                ),
                Text(
                  S.of(context).shipping_type,
                  style: const TextStyle(color: Color(0xff4B5563), fontSize: 18, fontWeight: FontWeight.w500),
                ),
                const SizedBox(
                  height: 10,
                ),
                BlocBuilder<OrderCubit, OrderState>(
                  builder: (context, state) {
                    int? selectedMethodId = context.read<OrderCubit>().selectedDeliveryMethodId;
                    
                    // Find selected address zone
                    String? addressId = context.read<ProfileCubit>().selectedAddress;
                    int? zoneId;
                    if (addressId != null) {
                      var addresses = context.read<ProfileCubit>().addressesModel?.data ?? [];
                      for (var addr in addresses) {
                        if (addr.id.toString() == addressId) {
                          zoneId = addr.deliveryZoneId;
                          break;
                        }
                      }
                    }

                    // Find delivery methods for this zone
                    List<dynamic> methods = [];
                    if (zoneId != null) {
                      var zones = context.read<OrderCubit>().deliveryZonesModel?.data ?? [];
                      for (var zone in zones) {
                        if (zone.id == zoneId) {
                          methods = zone.deliveryMethods ?? [];
                          break;
                        }
                      }
                    }

                    if (methods.isEmpty && addressId != null) {
                      return const Padding(
                        padding: EdgeInsets.all(8.0),
                        child: Text("No delivery methods available for this address.", style: TextStyle(color: Colors.red)),
                      );
                    } else if (addressId == null) {
                      return const Padding(
                        padding: EdgeInsets.all(8.0),
                        child: Text("Please select an address first to see delivery options.", style: TextStyle(color: Colors.grey)),
                      );
                    }

                    return ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemBuilder: (context, index) {
                          var method = methods[index];
                          double basePrice = double.tryParse(method.basePrice ?? '0') ?? 0;
                          double pricePerKg = double.tryParse(method.pricePerKg ?? '0') ?? 0;
                          double totalWeight = context.read<CartCubit>().totalWeight;
                          double calculatedCost = basePrice + (totalWeight * pricePerKg);

                          return InkWell(
                            borderRadius: BorderRadius.circular(10),
                            onTap: () {
                              context.read<OrderCubit>().setSelectedDeliveryMethodId(method.id);
                              if(BlocProvider.of<WalletCubit>(context).isSelected){
                                BlocProvider.of<CartCubit>(context).getTotals(double.tryParse(context.read<WalletCubit>().wallet?.data?.balance.toString()??'')??0,calculatedCost);
                              }else{
                                BlocProvider.of<CartCubit>(context).getTotals(0,calculatedCost);
                              }
                            },
                            child: ShippingTypeWidget(
                              value: method.id,
                              type: method.name,
                              numberOfDays: method.estimatedDays,
                              fees: "${calculatedCost.toStringAsFixed(2)} EUR",
                              selectedType: selectedMethodId,
                              onChanged: (value) {
                                context.read<OrderCubit>().setSelectedDeliveryMethodId(value);
                                if(BlocProvider.of<WalletCubit>(context).isSelected){
                                  BlocProvider.of<CartCubit>(context).getTotals(double.tryParse(context.read<WalletCubit>().wallet?.data?.balance.toString()??'')??0,calculatedCost);
                                }else{
                                  BlocProvider.of<CartCubit>(context).getTotals(0,calculatedCost);
                                }
                              },
                            ),
                          );
                        },
                        separatorBuilder: (context, index) => const SizedBox(
                              height: 10,
                            ),
                        itemCount: methods.length);
                  },
                ),
                const SizedBox(
                  height: 30,
                ),
                Text(
                  S.of(context).wallet,
                  style: const TextStyle(color: Color(0xff4B5563), fontSize: 18, fontWeight: FontWeight.w500),
                ),
                const SizedBox(
                  height: 10,
                ),
                BlocBuilder<WalletCubit, WalletState>(
                  builder: (context, state) {

                    return InkWell(
                      borderRadius: BorderRadius.circular(10),
                      onTap: () {
                        context.read<WalletCubit>().toggleIsSelected();
                        double taxes = context.read<CartCubit>().taxes; // Keep current shipping cost
                        if(context.read<WalletCubit>().isSelected){
                          BlocProvider.of<CartCubit>(context).getTotals(double.tryParse(context.read<WalletCubit>().wallet?.data?.balance.toString()??'')??0,taxes);
                        }else{
                          BlocProvider.of<CartCubit>(context).getTotals(0,taxes);
                        }
                      },
                      child: WalletUsageWidget(
                        value: context.read<WalletCubit>().isSelected,
                        amount: double.tryParse(context.read<WalletCubit>().wallet?.data?.balance.toString()??'')??0,
                        label: S.of(context).wallet,
                        onChanged: (value) {
                          context.read<WalletCubit>().toggleIsSelected();
                          double taxes = context.read<CartCubit>().taxes;
                          if(context.read<WalletCubit>().isSelected){
                            BlocProvider.of<CartCubit>(context).getTotals(double.tryParse(context.read<WalletCubit>().wallet?.data?.balance.toString()??'')??0,taxes);
                          }else{
                            BlocProvider.of<CartCubit>(context).getTotals(0,taxes);
                          }
                        },
                      ),
                    );
                  },
                ),
                const SizedBox(
                  height: 30,
                ),
                BlocBuilder<OrderCubit, OrderState>(
                  builder: (context, state) {
                    bool isLoading = context.read<OrderCubit>().isLoading;
                    return CustomCouponFormField(
                      title: S.of(context).coupon,
                      hint: S.of(context).coupon,
                      controller: context.read<OrderCubit>().couponController,
                      applyButtonChild: isLoading
                          ? const CircularProgressIndicator(
                              strokeWidth: 4,
                            )
                          : null,
                      onApplyPressed: isLoading
                          ? null
                          : () async {
                              var res = await context.read<OrderCubit>().applyCoupon(token: token);
                              var coupon = context.read<OrderCubit>().couponsModel;
                              if (res == null) {
                                double? minCartAmount = (double.tryParse(coupon?.data?.minimumCartAmount ?? ''));
                                double discountAmount = (double.tryParse(coupon?.data?.discountAmount ?? '') ?? 0);
                                String discountType = coupon?.data?.discountType ?? '';
                                String? vendorId = coupon?.data?.vendorId.toString();
                                if (minCartAmount != null && BlocProvider.of<CartCubit>(context).totalPrice >= minCartAmount) {
                                  BlocProvider.of<CartCubit>(context).applyDiscount(discountAmount, discountType, true, vendorId);
                                  context.read<OrderCubit>().setCouponMessage(S.of(context).coupon_applied, true);
                                } else {
                                  BlocProvider.of<CartCubit>(context).applyDiscount(discountAmount, discountType, false, vendorId);
                                  context.read<OrderCubit>().setCouponMessage(
                                        S.of(context).coupon_failed,
                                        false,
                                      );
                                }
                              } else if (res != null && coupon != null) {
                                BlocProvider.of<CartCubit>(context).applyDiscount(0, '0', false, null);
                                context.read<OrderCubit>().setCouponMessage("${S.of(context).coupon_failed}\n ${coupon.message}", false);
                              }
                            },
                    );
                  },
                ),
                BlocBuilder<OrderCubit, OrderState>(
                  builder: (context, state) {
                    bool isApplied = context.read<OrderCubit>().isCouponApplied;
                    return context.read<OrderCubit>().couponMessage == null
                        ? Container()
                        : Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const SizedBox(
                                height: 20,
                              ),
                              Card(
                                color: isApplied ? Colors.greenAccent : Colors.red,
                                child: Padding(
                                  padding: const EdgeInsets.all(8.0),
                                  child: Center(
                                    child: Text(
                                      context.read<OrderCubit>().couponMessage ?? '',
                                      style: const TextStyle(
                                        fontSize: 18,
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          );
                  },
                ),
                const SizedBox(
                  height: 20,
                ),

                BlocBuilder<CartCubit, CartState>(
                  builder: (context, state) {
                    MyCartModel? myCart = context.read<CartCubit>().myCart;
                    var cartData = myCart?.data;
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
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
                                ListView.builder(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  itemBuilder: (context, index) {
                                    return Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Row(
                                            children: [
                                              Expanded(
                                                child: Text(
                                                  "${locale == 'ar' ? (cartData?[index].product?.nameArabic) : (cartData?[index].product?.nameGerman)}",
                                                  style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                                ),
                                              ),
                                              Text(
                                                "x ${cartData?[index].quantity}",
                                                style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(
                                          width: 40,
                                        ),
                                        Text(
                                          "${context.read<CartCubit>().discountedItems[cartData?[index].id] ?? cartData?[index].price} EUR",
                                          style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                        ),
                                      ],
                                    );
                                  },
                                  itemCount: cartData?.length ?? 0,
                                ),
                                const SizedBox(
                                  height: 10,
                                ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      "${S.of(context).subtotal} (${cartData?.length})",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                    ),
                                    Text(
                                      "${context.read<CartCubit>().totalPrice.toStringAsFixed(2)} EUR",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                    ),
                                  ],
                                ),
                                const SizedBox(
                                  height: 10,
                                ),
                                context.read<CartCubit>().discount != 0
                                    ? Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            S.of(context).discount_optional,
                                            style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                          ),
                                          Text(
                                            "${context.read<CartCubit>().discount.toStringAsFixed(2)} EUR",
                                            style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                          ),
                                        ],
                                      )
                                    : Container(),
                                const SizedBox(
                                  height: 10,
                                ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      S.of(context).tax,
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                    ),
                                    Text(
                                      "${context.read<CartCubit>().taxes} EUR",
                                      style: TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
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
                                      "${context.read<CartCubit>().totalAfterDiscount.toStringAsFixed(2)} EUR",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 16, fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
        ),
        bottomNavigationBar: Container(
          height: 65,
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: BlocBuilder<OrderCubit, OrderState>(
            builder: (context, state) {
              bool isLoading = context.read<OrderCubit>().isLoading;
              return CustomSignIn_UpOne(
                customizeChild: isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                        color: Colors.white,
                      ))
                    : Text(
                        S.of(context).advance_order,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                title: S.of(context).advance_order,
                ontap: isLoading
                    ? null
                    : () async {
                        String? addressId = BlocProvider.of<ProfileCubit>(context).selectedAddress;
                        int? selectedMethodId = context.read<OrderCubit>().selectedDeliveryMethodId;
                        if (addressId == null) {
                          Helpers.showColoredToast(message: 'Please select an address!', color: Colors.red);
                          return;
                        }
                        if (selectedMethodId == null) {
                          Helpers.showColoredToast(message: 'Please select a delivery method!', color: Colors.red);
                          return;
                        }
                        bool isWalletSelected = BlocProvider.of<WalletCubit>(context).isSelected;
                        var res = await context.read<OrderCubit>().placeOrder(token: token, address_id: int.tryParse(addressId) ?? 0,wallet: isWalletSelected);
                        if (res == null) {
                          String paymentUrl = context.read<OrderCubit>().placeOrderModel?.data?.payment?.paymentUrl ?? '';
                          var response = await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => PaymentWebView(
                                  paymentUrl: paymentUrl,
                                ),
                              ));
                          if (response['status'] == 'success') {
                            Navigator.pushReplacement(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const OrderSuccessScreen(),
                              ),
                            );
                          } else {
                            Helpers.showColoredToast(message: "Payment Failed", color: Colors.red);
                          }
                          BlocProvider.of<CartCubit>(context).getMyCart(token: token);
                        }
                      },
              );
            },
          ),
        ),
      ),
    );
  }
}

class NoAddressesWidget extends StatelessWidget {
  const NoAddressesWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 50,
      child: Center(
        child: Text(
          S.of(context).no_saved_addresses,
          style: const TextStyle(color: Colors.grey, fontSize: 17, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}

class AddressItem extends StatelessWidget {
  AddressItem({
    super.key,
    this.value,
    this.address,
    this.selectedAddress,
    this.onDelete,
    required this.onChanged,
  });

  String? value;
  String? address;
  String? selectedAddress;
  void Function()? onDelete;
  void Function(String?) onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 10),
      decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: const Color(0xffEEEFF2))),
      child: Row(
        children: [
          CustomRadioWidget<String?>(
            width: 25,
            height: 25,
            value: value,
            groupValue: selectedAddress,
            onChanged: onChanged,
            unselectedBorderColor: const Color(0xff6B7280),
            unselectedInnerColor: Colors.white,
          ),
          Expanded(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    address ?? '',
                    style: const TextStyle(fontSize: 14),
                  ),
                ),
                IconButton(onPressed: onDelete, icon: const Icon(Icons.playlist_remove_outlined)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class ShippingTypeWidget extends StatelessWidget {
  ShippingTypeWidget({
    super.key,
    this.value,
    this.type,
    this.selectedType,
    this.numberOfDays,
    this.fees,
    required this.onChanged,
  });

  int? value;
  String? type;
  String? fees;
  int? selectedType;
  String? numberOfDays;

  void Function(int?) onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 10),
      decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: const Color(0xffEEEFF2))),
      child: Row(
        children: [
          CustomRadioWidget<int?>(
            width: 21,
            height: 21,
            value: value,
            groupValue: selectedType,
            onChanged: onChanged,
            unselectedBorderColor: const Color(0xff6B7280),
            unselectedInnerColor: Colors.white,
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  type ?? '',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                ),
                const SizedBox(
                  height: 5,
                ),
                Text(
                  "${S.of(context).shipping_details} ${numberOfDays ?? 'xx'} ${S.of(context).days}",
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w400, color: Colors.grey[500]!),
                ),
              ],
            ),
          ),
          Text(
            fees ?? '',
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }
}

class WalletUsageWidget extends StatelessWidget {
  WalletUsageWidget({
    super.key,
    this.value,
    this.label,
    this.amount,
    required this.onChanged,
  });

  final bool? value; // هل مختار ولا لأ
  final String? label; // اسم الخيار
  final double? amount; // القيمة/الرصيد

  final void Function(bool?) onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 10),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xffEEEFF2)),
      ),
      child: Row(
        children: [
          SizedBox(
            width: 21,
            height: 21,
            child: Checkbox(
              value: value ?? false,
              onChanged: onChanged,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(4),
              ),
              side: const BorderSide(color: Color(0xff6B7280)),
              activeColor: Theme.of(context).primaryColor,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              label ?? S.of(context).wallet,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
            ),
          ),
          Text(
            amount.toString()??'0.0',
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }
}
