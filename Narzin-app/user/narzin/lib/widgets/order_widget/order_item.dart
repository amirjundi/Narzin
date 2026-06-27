import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:shimmer/shimmer.dart';

import '../../core/helpers.dart';
import '../../generated/assets.dart';

class OrderItem extends StatelessWidget {
  const OrderItem({
    super.key,
    required this.orderNumber,
    required this.numberOfItems,
    required this.imageUrl,
    required this.totalPrice,
    required this.status,
  });

  final String orderNumber;
  final String numberOfItems;
  final String imageUrl;
  final String totalPrice;
  final String status;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
      constraints: const BoxConstraints(minHeight: 50,),
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
                      orderNumber,
                      style: const TextStyle(color: Colors.black, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(
                      height: 5,
                    ),
                    Text(
                      'نقد عند التسليم',
                      style: TextStyle(color: Colors.grey[700], fontWeight: FontWeight.normal, fontSize: 15),
                    ),
                    const SizedBox(
                      height: 5,
                    ),
                    Text(
                      'EUR $totalPrice',
                      style: TextStyle(color: Constants.mainColor, fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios_rounded)
            ],
          ),
          (Helpers.orderStatus[status] == 0 || Helpers.orderStatus[status] == 9)?Container():const SizedBox(height: 20,),
          (Helpers.orderStatus[status] == 0 || Helpers.orderStatus[status] == 9)?Container():Column(
            children: [
              // Nodes and Dividers
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Helpers.orderStatus[status] == 1?const CurrentNodeWidget() : const DoneNodeWidget(),
                    Expanded(
                      child: SizedBox(
                        height: 25,
                        child: Divider(color: Constants.mainColor),
                      ),
                    ),
                    (Helpers.orderStatus[status]??0) == 2? const CurrentNodeWidget():(Helpers.orderStatus[status]??0) < 2?const NotReachedNodeWidget():const DoneNodeWidget(),
                    Expanded(
                      child: SizedBox(
                        height: 25,
                        child: Divider(color: (Helpers.orderStatus[status]??0) == 2?Constants.mainColor:(Helpers.orderStatus[status]??0) < 2? Colors.grey[300]:Constants.mainColor),
                      ),
                    ),
                    (Helpers.orderStatus[status]??0) == 3? const CurrentNodeWidget():(Helpers.orderStatus[status]??0) < 3?const NotReachedNodeWidget():const DoneNodeWidget(),
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
                      style: TextStyle(fontSize: 10,color: Constants.mainColor),
                      textAlign: TextAlign.start,
                    ),
                  ),
                  Expanded(
                    child: Center(
                      child: Text(
                        S.of(context).out_for_delivery,
                        style: TextStyle(fontSize: 10,color:(Helpers.orderStatus[status]??0) == 2?Constants.mainColor:(Helpers.orderStatus[status]??0) < 2? Colors.grey[300]:Constants.mainColor),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Text(
                      S.of(context).delivered,
                      style: TextStyle(fontSize: 10,color:(Helpers.orderStatus[status]??0) == 3?Constants.mainColor:(Helpers.orderStatus[status]??0) < 3? Colors.grey[300]:Constants.mainColor),
                      textAlign: TextAlign.end,
                    ),
                  ),
                ],
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
        Container(
          height: ScreenSizing.width * 0.25,
          width: ScreenSizing.width * 0.35,
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[500]!), color: Colors.black38),
          alignment: Alignment.center,
          child: Text(
            numberOfItems,
            style: const TextStyle(color: Colors.white, fontSize: 25, fontWeight: FontWeight.w800),
          ),
        ),
      ],
    );
  }
}


class CurrentNodeWidget extends StatelessWidget {
  const CurrentNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
      child: Center(
        child: Container(
          width: 5,
          height: 5,
          decoration: BoxDecoration(color: Constants.mainColor, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
        ),
      ),
    );
  }
}

class DoneNodeWidget extends StatelessWidget {
  const DoneNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Constants.mainColor, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
      child: const Center(
        child: Icon(
          Icons.check,
          size: 17,
          color: Colors.white,
          weight: 10,
        ),
      ),
    );
  }
}

class NotReachedNodeWidget extends StatelessWidget {
  const NotReachedNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(100), border: Border.all(color: Colors.grey[300]!)),
    );
  }
}

class OrdersShimmer extends StatelessWidget {
  const OrdersShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: 6, // Simulating 6 orders
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      itemBuilder: (context, index) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.grey[100]!,
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.1),
                    blurRadius: 6,
                    spreadRadius: 2,
                    offset: const Offset(0, 2),
                  )
                ],
              ),
              child: Row(
                children: [
                  // Placeholder for Order Image
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  const SizedBox(width: 16),

                  // Order Details Skeleton
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Order Title Placeholder
                        Container(
                          height: 14,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: Colors.grey[300],
                            borderRadius: BorderRadius.circular(5),
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Order Price Placeholder
                        Container(
                          height: 12,
                          width: 80,
                          decoration: BoxDecoration(
                            color: Colors.grey[300],
                            borderRadius: BorderRadius.circular(5),
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Order Delivery Date Placeholder
                        Container(
                          height: 12,
                          width: 120,
                          decoration: BoxDecoration(
                            color: Colors.grey[300],
                            borderRadius: BorderRadius.circular(5),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(width: 16),

                  // Order Status Placeholder
                  Container(
                    width: 70,
                    height: 25,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(5),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}