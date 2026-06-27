import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/vendor_stats_cubits/vendor_stats_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/main_app_vendor/orders_screens/single_order_screen.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/oreder_item.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/stats_item.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bussiness_logic/profile_cubits/profile_cubit.dart';
import '../../../generated/l10n.dart';
import '../../../model_layer/order_model.dart';
import '../../../widgets/image_widgets/insta_image_widget.dart';

class VendorHomeScreen extends StatefulWidget {
  VendorHomeScreen({super.key});

  @override
  State<VendorHomeScreen> createState() => _VendorHomeScreenState();
}

class _VendorHomeScreenState extends State<VendorHomeScreen> {
  @override
  void initState() {
    String token = BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '';
    BlocProvider.of<VendorStatsCubit>(context).getStatistics(token: token);
    BlocProvider.of<VendorStatsCubit>(context).getOrders(token: token);
    super.initState();
  }

  List<String> statsItems = [];

  @override
  Widget build(BuildContext context) {
    statsItems = [S.of(context).daily, S.of(context).weekly, S.of(context).monthly];
    // print(ScreenSizing.width * 0.43);
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          BlocBuilder<ProfileCubit, ProfileState>(
            builder: (context, state) {
              return Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  CircleAvatar(
                    backgroundColor: Colors.grey[300],
                    radius: 29,
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(100),
                      child: SizedBox(
                        height: 57,
                        width: 57,
                        child: InstaNetworkImageWidget(
                          imageUrl: 'https://admin.narzin.com/storage/${BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.storeLogo ?? ''}',
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      minTileHeight: kToolbarHeight * 1.2,
                      title: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 5.0),
                        child: Row(
                          children: [
                            Text(
                              "${S.of(context).welcome_message} ",
                              style: TextStyle(fontSize: 13, color: Colors.grey[700], fontWeight: FontWeight.w400),
                            ),
                            Text(
                              S.of(context).app_name,
                              style: TextStyle(fontSize: 15, color: Colors.grey[700]),
                            ),
                          ],
                        ),
                      ),
                      subtitle: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 5.0),
                        child: Text(
                          context.read<ProfileCubit>().profile?.data?.user?.name ?? 'Not available',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ),
                      trailing: IconButton(
                        style: IconButton.styleFrom(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10), side: BorderSide(color: Colors.grey[300]!)),
                        ),
                        onPressed: () {},
                        icon: const Icon(
                          Icons.notifications_active_outlined,
                          size: 30,
                        ),
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
          const SizedBox(
            height: 30,
          ),
          TextFormField(
            decoration: InputDecoration(
              prefixIcon: const Icon(Icons.search),
              hintText: S.of(context).search_placeholder,
              hintStyle: TextStyle(color: Colors.grey[500], fontSize: 14),
              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(40),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(40),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
            ),
          ),
          const SizedBox(
            height: 30,
          ),
          Container(
            height: 50,
            width: ScreenSizing.width,
            padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey[200]!),
              borderRadius: BorderRadius.circular(15),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Text(
                  S.of(context).statistics,
                  style: const TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w300,
                  ),
                ),
                BlocBuilder<VendorStatsCubit, VendorStatsState>(
                  builder: (context, state) {
                    return DropdownButton<String>(
                      padding: EdgeInsets.zero,
                      isDense: true,
                      items: statsItems
                          .map((e) => DropdownMenuItem(
                                value: e,
                                child: Text(
                                  e,
                                  style: TextStyle(color: Colors.grey[700]),
                                ),
                              ))
                          .toList(),
                      underline: const SizedBox(),
                      value: context.read<VendorStatsCubit>().selectedDropDownVal,
                      onChanged: (value) {
                        context.read<VendorStatsCubit>().setSelectedValue(value);
                      },
                    );
                  },
                )
              ],
            ),
          ),
          const SizedBox(
            height: 20,
          ),
          BlocBuilder<VendorStatsCubit, VendorStatsState>(
            builder: (context, state) {
              bool isLoading = context.read<VendorStatsCubit>().isLoading;
              String recentOrders = (context.read<VendorStatsCubit>().statistics?.data?.recentOrders ?? "0");
              String deliveredOrders = (context.read<VendorStatsCubit>().statistics?.data?.delivered ?? "0");
              String pendingOrders = (context.read<VendorStatsCubit>().statistics?.data?.pending ?? "0");
              String processingOrders = (context.read<VendorStatsCubit>().statistics?.data?.processing ?? "0");
              String shippedOrders = (context.read<VendorStatsCubit>().statistics?.data?.shipped ?? "0");
              String totalRevenue = (context.read<VendorStatsCubit>().statistics?.data?.totalRevenue ?? '0');
              String totalProfit = (context.read<VendorStatsCubit>().statistics?.data?.totalProfit ?? '0').toString();
              String totalCost = (context.read<VendorStatsCubit>().statistics?.data?.totalCost ?? '0').toString();

              if (isLoading) {
                return const StatsLoadingWidget();
              }
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      StatsItem(
                        asset: Assets.appIconsPackage,
                        title: S.of(context).total_orders,
                        value: recentOrders.toString(),
                      ),

                      const Spacer(),
                      StatsItem(
                        asset: Assets.appIconsSoldStats,
                        title: S.of(context).total_sales,
                        value: totalRevenue,
                      ),

                    ],
                  ),
                  const SizedBox(
                    height: 5,
                  ),
                  Row(
                    children: [
                      StatsItem(
                        asset: Assets.appIconsWaiting,
                        title: S.of(context).total_profit,
                        value: totalProfit,
                      ),
                      const Spacer(),
                      StatsItem(
                        asset: Assets.appIconsUsers,
                        title: S.of(context).total_cost,
                        value: totalCost,
                      ),
                    ],
                  ),
                ],
              );
            },
          ),
          const SizedBox(
            height: 20,
          ),
          Row(
            children: [
              Text(
                S.of(context).new_orders,
                style: const TextStyle(fontSize: 17),
              )
            ],
          ),
          const SizedBox(
            height: 10,
          ),
          BlocBuilder<VendorStatsCubit, VendorStatsState>(
            builder: (context, state) {
              String locale = BlocProvider.of<LocalizationCubit>(context).locale;
              bool isLoading = context.read<VendorStatsCubit>().isLoading;
              if (isLoading) {
                return  Shimmer.fromColors(
                    highlightColor: Colors.white,
                    baseColor: Colors.grey[200]!,child: const OrderItem());
              }
              return ListView.builder(
                itemCount: context.read<VendorStatsCubit>().orders?.data?.orders?.data?.length??0,
                itemBuilder: (context, index) {
                  var item = context.read<VendorStatsCubit>().orders?.data?.orders?.data?[index];
                  return InkWell(
                    onTap: () {
                      context.read<VendorStatsCubit>().setSelectedOrder(item);
                      Navigator.push(context, MaterialPageRoute(builder: (context) => const SingleOrderScreen()));
                    },
                    child: OrderItem(
                      orderNumber:item?.orderNumber.toString(),
                      locale: locale,
                      orderItem: item?.items,
                      total: item?.totalAmount.toString(),
                    ),
                  );
                },
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
              );
            },
          )
        ],
      ),
    );
  }
}

class StatsLoadingWidget extends StatelessWidget {
  const StatsLoadingWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            Shimmer.fromColors(
              highlightColor: Colors.white,
              baseColor: Colors.grey[200]!,
              child: StatsItem(
                asset: Assets.appIconsUsers,
                title: S.of(context).total_users,
                value: '40,300',
              ),
            ),
            const Spacer(),
            Shimmer.fromColors(
              highlightColor: Colors.white,
              baseColor: Colors.grey[200]!,
              child: StatsItem(
                asset: Assets.appIconsPackage,
                title: S.of(context).total_orders,
                value: '40,300',
              ),
            ),
          ],
        ),
        const SizedBox(
          height: 5,
        ),
        Shimmer.fromColors(
          highlightColor: Colors.white,
          baseColor: Colors.grey[200]!,
          child: Row(
            children: [
              StatsItem(
                asset: Assets.appIconsWaiting,
                title: S.of(context).total_pending,
                value: '40,300',
              ),
              const Spacer(),
              Shimmer.fromColors(
                highlightColor: Colors.white,
                baseColor: Colors.grey[200]!,
                child: StatsItem(
                  asset: Assets.appIconsSoldStats,
                  title: S.of(context).total_sales,
                  value: '40,300',
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
