import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/my_orders_model.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/my_orders_screen/single_order_screen.dart';
import 'package:narzin/widgets/order_widget/order_item.dart';

import '../../../../core/helpers.dart';

class MyOrdersScreen extends StatelessWidget {
  const MyOrdersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).orders,
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
          List<MyOrder>? myOrders = context.read<OrderCubit>().myOrdersModel?.data?.data;
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
                    S.of(context).current_shipping,
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
                      print(Helpers.orderStatus[status]);
                      String totalPrice = myOrders?[index].totalAmount.toString() ?? '0';
                      String imageUrl = myOrders?[index].items?.firstOrNull?.product?.images?.firstOrNull?.url ?? '';
                      return Helpers.orderStatus[status] == 0 || Helpers.orderStatus[status] == 9?
                      Container():
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 10.0),
                        child: InkWell(
                          onTap: () {
                            String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                            context.read<OrderCubit>().getSingleOrder(token: token,id: myOrders?[index].id);
                            Navigator.push(context, MaterialPageRoute(builder: (context) => SingleOrderScreen(order: myOrders?[index],),),);
                          },
                          child: OrderItem(
                            numberOfItems: "$numberOfItems +",
                            imageUrl: imageUrl,
                            orderNumber: orderNumber,
                            status: status,
                            totalPrice: totalPrice,
                          ),
                        ),
                      );
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




