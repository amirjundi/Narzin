import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/my_orders_model.dart';
import 'package:shimmer/shimmer.dart';

import '../../../../core/constants.dart';
import '../../../../generated/assets.dart';
import '../../../../widgets/order_widget/order_item.dart';

class SingleOrderScreen extends StatelessWidget {
  const SingleOrderScreen({super.key, required this.order});

  final MyOrder? order;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: BlocBuilder<OrderCubit, OrderState>(
          builder: (context, state) {
            return Text(
              order?.orderNumber ?? '',
              style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 15),
            );
          },
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        actions: [
          BlocBuilder<OrderCubit, OrderState>(
            builder: (context, state) {
              return IconButton(
                onPressed: () {
                  showMenu(
                    context: context,
                    position: RelativeRect.fromLTRB(0, kToolbarHeight * 1.33, ScreenSizing.width, 0), // Position of the dropdown
                    items: [
                      PopupMenuItem(
                        child: Text(S.of(context).cancel),
                        onTap: () async {
                          String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                          var res = await context.read<OrderCubit>().changeOrderStatus(token: token, id: order?.id);
                          if (res == null) {
                            context.read<OrderCubit>().getSingleOrder(token: token, id: order?.id);
                          }
                        },
                      ),
                    ],
                  );
                  // Navigator.canPop(context) ? Navigator.pop(context) : null;
                },
                icon: const Icon(Icons.more_vert_sharp),
              );
            },
          ),
        ],
        centerTitle: true,
      ),
      body: BlocBuilder<OrderCubit, OrderState>(
        builder: (context, state) {
          bool isLoading = context.read<OrderCubit>().isLoading;
          String locale = BlocProvider.of<LocalizationCubit>(context).locale;
          var singleOrder = context.read<OrderCubit>().singleOrder;
          var items = singleOrder?.data?.items;
          return Container(
            height: ScreenSizing.height,
            width: ScreenSizing.width,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 5),
            child: isLoading
                ? const OrderDetailsShimmer()
                : SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemBuilder: (context, index) {
                            String productName = locale == 'ar' ? (items?[index].product?.nameArabic ?? '') : (items?[index].product?.nameGerman ?? '');
                            return OrderDetailedItem(
                              onPressed: () {},
                              numberOfItems: "3",
                              imageUrl: items?[index].product?.images?.firstOrNull?.url ?? '',
                              itemName: productName,
                              status: items?[index].status ?? '',
                              vendorName: items?[index].vendorId.toString() ?? '',
                              itemPrice: items?[index].productVariant?.price ?? '',
                            );
                          },
                          separatorBuilder: (context, index) => const SizedBox(
                            height: 5,
                          ),
                          itemCount: items?.length ?? 0,
                        ),
                        const SizedBox(
                          height: 25,
                        ),
                        const Divider(),
                        const SizedBox(
                          height: 25,
                        ),
                        Text(
                          S.of(context).status,
                          style: const TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w500,
                            color: Color(0xff4B5563),
                          ),
                        ),
                        TrackingWidget(status: order?.orderStatus ?? ''),
                        const SizedBox(
                          height: 25,
                        ),
                        const Divider(),
                        const SizedBox(
                          height: 25,
                        ),
                        Text(
                          S.of(context).delivery_to,
                          style: TextStyle(
                            color: Colors.grey[700]!,
                            fontSize: 17,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(
                          height: 25,
                        ),
                        Text(
                          Helpers.formatLangFullAddress(location: order?.address?.address.toString() ?? ''),
                          style: TextStyle(color: Colors.grey[800]),
                          textAlign: TextAlign.justify,
                        ),
                        const SizedBox(
                          height: 25,
                        ),
                        const Divider(),
                        const SizedBox(
                          height: 20,
                        ),
                        Text(
                          S.of(context).order_summary,
                          style: TextStyle(
                            color: Colors.grey[700]!,
                            fontSize: 17,
                            fontWeight: FontWeight.w500,
                          ),
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
                                      "${S.of(context).subtotal} (${order?.items?.length ?? 0})",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                    ),
                                    Text(
                                      "${order?.totalAmount ?? 0} EUR",
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
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
                                    ),
                                    Text(
                                      "${order?.shippingCost ?? 0} EUR",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 14, fontWeight: FontWeight.w400),
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
                                      "${order?.finalPrice ?? 0} EUR",
                                      style: const TextStyle(color: Color(0xff000000), fontSize: 16, fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
          );
        },
      ),
    );
  }
}

class OrderDetailsShimmer extends StatelessWidget {
  const OrderDetailsShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.grey[100]!,
            child: Container(
              width: double.infinity,
              height: 20.0,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 10),
          Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.grey[100]!,
            child: Container(
              width: 200.0,
              height: 15.0,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 20),
          ...List.generate(10, (index) => buildShimmerRow()),
        ],
      ),
    );
  }

  Widget buildShimmerRow() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.grey[100]!,
            child: Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8.0),
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Shimmer.fromColors(
                  baseColor: Colors.grey[300]!,
                  highlightColor: Colors.grey[100]!,
                  child: Container(
                    width: double.infinity,
                    height: 15.0,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 5),
                Shimmer.fromColors(
                  baseColor: Colors.grey[300]!,
                  highlightColor: Colors.grey[100]!,
                  child: Container(
                    width: 100.0,
                    height: 10.0,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class OrderDetailedItem extends StatelessWidget {
  const OrderDetailedItem({
    super.key,
    required this.itemName,
    required this.numberOfItems,
    required this.imageUrl,
    required this.itemPrice,
    required this.status,
    required this.vendorName,
    required this.onPressed,
  });

  final String itemName;
  final String numberOfItems;
  final String imageUrl;
  final String itemPrice;
  final String status;
  final String vendorName;
  final void Function()? onPressed;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
      constraints: const BoxConstraints(
        minHeight: 50,
      ),
      width: ScreenSizing.width,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              OrderImageWidget(
                url: imageUrl,
                numberOfItems: numberOfItems,
              ),
              const SizedBox(
                width: 10,
              ),
              Expanded(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      itemName,
                      style: const TextStyle(color: Colors.black, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(
                      height: 5,
                    ),
                    Text(
                      'تم البيع بواسطة $vendorName',
                      style: TextStyle(color: Colors.grey[700], fontWeight: FontWeight.normal, fontSize: 15),
                    ),
                    const SizedBox(
                      height: 5,
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'EUR $itemPrice',
                          style: TextStyle(color: Constants.mainColor, fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        TextButton(
                            style: TextButton.styleFrom(
                              padding: EdgeInsets.zero,
                            ),
                            onPressed: onPressed,
                            child: Text(
                              S.of(context).cancel,
                              style: const TextStyle(fontSize: 16, color: Colors.red, fontWeight: FontWeight.w500, decoration: TextDecoration.underline, decorationColor: Colors.red),
                            ))
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class OrderImageWidget extends StatelessWidget {
  const OrderImageWidget({
    super.key,
    this.url,
    this.numberOfItems = '0',
  });

  final String? url;
  final String numberOfItems;

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: CachedNetworkImage(
            imageUrl: url ?? '',
            colorBlendMode: BlendMode.darken,
            height: ScreenSizing.width * 0.25,
            width: ScreenSizing.width * 0.35,
            fit: BoxFit.cover,
            placeholder: (context, url) => const Center(
              child: CircularProgressIndicator(),
            ),
            errorWidget: (context, url, error) => Image.asset(
              Assets.imagesProductPlaceholder2,
              fit: BoxFit.cover,
            ),
          ),
        ),
      ],
    );
  }
}

class TrackingWidget extends StatelessWidget {
  const TrackingWidget({super.key, required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        (Helpers.orderStatus[status] == 0 || Helpers.orderStatus[status] == 9)
            ? Container()
            : const SizedBox(
                height: 20,
              ),
        (Helpers.orderStatus[status] == 0 || Helpers.orderStatus[status] == 9)
            ? Container()
            : Column(
                children: [
                  // Nodes and Dividers
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 10),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Helpers.orderStatus[status] == 1 ? const CurrentNodeWidget() : const DoneNodeWidget(),
                        Expanded(
                          child: SizedBox(
                            height: 25,
                            child: Divider(color: Constants.mainColor),
                          ),
                        ),
                        (Helpers.orderStatus[status] ?? 0) == 2
                            ? const CurrentNodeWidget()
                            : (Helpers.orderStatus[status] ?? 0) < 2
                                ? const NotReachedNodeWidget()
                                : const DoneNodeWidget(),
                        Expanded(
                          child: SizedBox(
                            height: 25,
                            child: Divider(
                                color: (Helpers.orderStatus[status] ?? 0) == 2
                                    ? Constants.mainColor
                                    : (Helpers.orderStatus[status] ?? 0) < 2
                                        ? Colors.grey[300]
                                        : Constants.mainColor),
                          ),
                        ),
                        (Helpers.orderStatus[status] ?? 0) == 3
                            ? const CurrentNodeWidget()
                            : (Helpers.orderStatus[status] ?? 0) < 3
                                ? const NotReachedNodeWidget()
                                : const DoneNodeWidget(),
                      ],
                    ),
                  ),
                  // Labels Below Each Node
                  Row(
                    mainAxisAlignment: MainAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          S.of(context).pending,
                          style: TextStyle(fontSize: 10, color: Constants.mainColor),
                          textAlign: TextAlign.start,
                        ),
                      ),
                      Expanded(
                        child: Center(
                          child: Text(
                            S.of(context).out_for_delivery,
                            style: TextStyle(
                                fontSize: 10,
                                color: (Helpers.orderStatus[status] ?? 0) == 2
                                    ? Constants.mainColor
                                    : (Helpers.orderStatus[status] ?? 0) < 2
                                        ? Colors.grey[300]
                                        : Constants.mainColor),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                      Expanded(
                        child: Text(
                          S.of(context).delivered,
                          style: TextStyle(
                              fontSize: 10,
                              color: (Helpers.orderStatus[status] ?? 0) == 3
                                  ? Constants.mainColor
                                  : (Helpers.orderStatus[status] ?? 0) < 3
                                      ? Colors.grey[300]
                                      : Constants.mainColor),
                          textAlign: TextAlign.end,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
      ],
    );
  }
}
