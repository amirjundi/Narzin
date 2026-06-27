import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/widgets/order_widget/order_item.dart';

import '../../../../bussiness_logic/order_cubits/order_cubit.dart';
import '../../../../core/constants.dart';
import '../../../../core/helpers.dart';
import '../../../../core/screen_sizing_constants.dart';
import '../../../../generated/l10n.dart';

class ReturnsScreen extends StatelessWidget {
  const ReturnsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).returns,
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
            },
            icon: const Icon(Icons.more_vert_sharp),
          ),
        ],
        centerTitle: true,
      ),
      body: BlocBuilder<OrderCubit, OrderState>(
        builder: (context, state) {
          var myOrders = context.read<OrderCubit>().myOrdersModel?.data?.data;
          bool isLoading = context.read<OrderCubit>().isLoading;
          return Container(
            height: ScreenSizing.height,
            width: ScreenSizing.width,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            child: isLoading? const OrdersShimmer(): SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    "${S.of(context).returns} & ${S.of(context).cancelled_operations}",
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w500,
                      color: Color(0xff4B5563),
                    ),
                  ),
                  const SizedBox(height: 10,),
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: myOrders?.length??0,
                    itemBuilder: (context, index) {
                      String numberOfItems = myOrders?[index].items?.length.toString() ?? '0';
                      String orderNumber = myOrders?[index].orderNumber.toString() ?? '0';
                      String status = myOrders?[index].orderStatus.toString() ?? '0';
                      print(status);
                      String totalPrice = myOrders?[index].totalAmount.toString() ?? '0';
                      String imageUrl = myOrders?[index].items?.firstOrNull?.product?.images?.firstOrNull?.url ?? '';
                      return (Helpers.orderStatus[status] == 0) || (Helpers.orderStatus[status] == 9)?
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 10.0),
                        child: OrderItem(
                          numberOfItems: "$numberOfItems +",
                          imageUrl: imageUrl,
                          orderNumber: orderNumber,
                          status: status,
                          totalPrice: totalPrice,
                        ),
                      ): Container();
                    },

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
