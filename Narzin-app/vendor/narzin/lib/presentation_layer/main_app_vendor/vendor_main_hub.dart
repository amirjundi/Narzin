import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';

class VendorMainHub extends StatelessWidget {
  const VendorMainHub({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BlocBuilder<MainHubCubit, MainHubState>(
        builder: (context, state) {
          return SafeArea(
            child: Container(
              width: ScreenSizing.width,
              padding: EdgeInsets.symmetric(horizontal: 15,vertical: 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Expanded(child: context.read<MainHubCubit>().vendorMainAppScreens[context.read<MainHubCubit>().currentIndex]),
                ],
              ),
            ),
          );
        },
      ),
      bottomNavigationBar: BlocBuilder<MainHubCubit, MainHubState>(
        builder: (context, state) {
          return BottomNavigationBar(
            backgroundColor: Colors.white,
            selectedFontSize: 16,
            currentIndex: context.read<MainHubCubit>().currentIndex,
            onTap: (index) async {
              if(index == 1){
                BlocProvider.of<ProductCubit>(context).getVendorProducts(vendor_id: BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.id);
                await BlocProvider.of<ProductCubit>(context).getCategories();
              }
              context.read<MainHubCubit>().setCurrentIndex(index);
            },
            type: BottomNavigationBarType.fixed,
            selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold),
            items: [
              BottomNavigationBarItem(
                backgroundColor: Constants.mainColor,
                activeIcon: Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(shape: BoxShape.rectangle, borderRadius: BorderRadius.circular(20), color: Constants.lightSecondaryColor),
                  child: SvgPicture.asset(
                    Assets.appIconsSelectedHome,
                  ),
                ),
                icon: SvgPicture.asset(
                  Assets.appIconsHomeIcon,
                ),
                label: S.of(context).home,
              ),
              BottomNavigationBarItem(
                activeIcon: Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(shape: BoxShape.rectangle, borderRadius: BorderRadius.circular(20), color: Constants.lightSecondaryColor),
                  child: SvgPicture.asset(
                    Assets.appIconsSelectedCategory,
                  ),
                ),
                icon: SvgPicture.asset(Assets.appIconsCategoryIcon),
                label: S.of(context).products,
              ),
              BottomNavigationBarItem(
                activeIcon: Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(shape: BoxShape.rectangle, borderRadius: BorderRadius.circular(20), color: Constants.lightSecondaryColor),
                  child: SvgPicture.asset(
                    Assets.appIconsSelectedOrdersIcon,
                  ),
                ),
                icon: SvgPicture.asset(Assets.appIconsOrdersIcon),
                label: S.of(context).orders,
              ),
              BottomNavigationBarItem(
                activeIcon: Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(shape: BoxShape.rectangle, borderRadius: BorderRadius.circular(20), color: Constants.lightSecondaryColor),
                  child: SvgPicture.asset(
                    Assets.appIconsSelectedProfile,
                    color: Constants.mainColor,
                  ),
                ),
                icon: SvgPicture.asset(Assets.appIconsProfile),
                label: S.of(context).my_account,
              ),
            ],
          );
        },
      ),
    );
  }
}
