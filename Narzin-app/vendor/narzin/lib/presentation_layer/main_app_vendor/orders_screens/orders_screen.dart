import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/bussiness_logic/vendor_stats_cubits/vendor_stats_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/presentation_layer/main_app_vendor/orders_screens/single_order_screen.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/oreder_item.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bussiness_logic/localization_cubit/localization_cubit.dart';
import '../../../bussiness_logic/login_cubits/login_cubit.dart';
import '../../../widgets/image_widgets/insta_image_widget.dart';

class OrdersScreen extends StatefulWidget {
  OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  @override
  void initState() {
    super.initState();
    String token = BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '';
    BlocProvider.of<VendorStatsCubit>(context).getOrders(token: token);
  }

  List<String> statsItems = [];

  List<String> filtersTitles = [];

  List<String> filtersIcons = [];

  @override
  Widget build(BuildContext context) {
    statsItems = [S.of(context).daily, S.of(context).weekly, S.of(context).monthly];
    filtersTitles = [
      S.of(context).status_new,
      S.of(context).returns,
      S.of(context).status_completed,
      S.of(context).status_cancelled,
    ];
    filtersIcons = [
      Assets.appIconsNewIcon,
      Assets.appIconsActiveIcon,
      Assets.appIconsCompletedIcon,
      Assets.appIconsCancelIcon,
    ];
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
          SizedBox(
            height: 20,
          ),
          Container(
            height: 40,
            width: ScreenSizing.width,
            child: BlocBuilder<VendorStatsCubit, VendorStatsState>(
              builder: (context, state) {
                return ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: filtersTitles.length,
                  itemBuilder: (context, index) {
                    return InkWell(
                      onTap: () {
                        context.read<VendorStatsCubit>().setSelectedFilterIndex(index);
                      },
                      child: Container(
                        margin: const EdgeInsets.only(left: 7),
                        padding: EdgeInsets.symmetric(horizontal: 5, vertical: 5),
                        height: 50,
                        constraints: BoxConstraints(minWidth: 100),
                        decoration: context.read<VendorStatsCubit>().selectedFilterIndex == index
                            ? BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [
                                    Color(0xff3084C2),
                                    Color(0xff5BB5EF),
                                  ],
                                ),
                                borderRadius: BorderRadius.circular(15))
                            : BoxDecoration(
                                borderRadius: BorderRadius.circular(15),
                                border: Border.all(
                                  color: Colors.grey[300]!,
                                ),
                              ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            SvgPicture.asset(
                              filtersIcons[index],
                              color: context.read<VendorStatsCubit>().selectedFilterIndex == index ? Colors.white : Color(0xff5BB5EF),
                            ),
                            SizedBox(width: 2),
                            Text(
                              filtersTitles[index],
                              style: context.read<VendorStatsCubit>().selectedFilterIndex == index ? TextStyle(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600) : TextStyle(fontSize: 17, color: Color(0xff5BB5EF)),
                            )
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
          const SizedBox(
            height: 20,
          ),
          Row(
            children: [
              SvgPicture.asset(Assets.appIconsTools),
              Text(
                S.of(context).sort_by,
                style: TextStyle(color: Colors.black, fontWeight: FontWeight.w600),
              ),
              BlocBuilder<VendorStatsCubit, VendorStatsState>(
                builder: (context, state) {
                  return DropdownButton<String>(
                    padding: EdgeInsets.zero,
                    isDense: true,
                    items: statsItems
                        .map(
                          (e) => DropdownMenuItem(
                            value: e,
                            child: Text(
                              e,
                              style: TextStyle(color: Colors.black, fontWeight: FontWeight.w600),
                            ),
                          ),
                        )
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
          const SizedBox(
            height: 10,
          ),
          BlocBuilder<VendorStatsCubit, VendorStatsState>(
            builder: (context, state) {
              String locale = BlocProvider.of<LocalizationCubit>(context).locale;
              bool isLoading = context.read<VendorStatsCubit>().isLoading;
              if (isLoading) {
                return Shimmer.fromColors(highlightColor: Colors.white, baseColor: Colors.grey[200]!, child: OrderItem());
              }
              return ListView.builder(
                itemCount: context.read<VendorStatsCubit>().orders?.data?.orders?.data?.length ?? 0,
                itemBuilder: (context, index) {
                  var item = context.read<VendorStatsCubit>().orders?.data?.orders?.data?[index];
                  // print(Constants.orderStatus[context.read<VendorStatsCubit>().selectedFilterIndex]);
                  // print(item?.orderStatus);
                  return Constants.orderStatus[context.read<VendorStatsCubit>().selectedFilterIndex] == (item?.orderStatus)?
                  InkWell(
                    onTap: () {
                      context.read<VendorStatsCubit>().setSelectedOrder(item);
                      Navigator.push(context, MaterialPageRoute(builder: (context) => const SingleOrderScreen()));
                    },
                    child: OrderItem(
                      orderNumber: item?.orderNumber.toString(),
                      locale: locale,
                      orderItem: item?.items,
                      total: item?.totalAmount.toString(),
                    ),
                  ):Container();
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

class SearchWidget extends StatelessWidget {
  const SearchWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
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
      ],
    );
  }
}
