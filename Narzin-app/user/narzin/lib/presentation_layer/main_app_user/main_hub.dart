import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/generated/assets.dart';

import '../../core/constants.dart';
import '../../generated/l10n.dart';

class MainHub extends StatelessWidget {
  const MainHub({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BlocBuilder<MainHubCubit, MainHubState>(
        builder: (context, state) {
          return context.read<MainHubCubit>().mainAppScreens[context.read<MainHubCubit>().currentIndex];
        },
      ),
      bottomNavigationBar: BlocBuilder<MainHubCubit, MainHubState>(
        builder: (context, state) {
          return BottomNavigationBar(
            backgroundColor: Colors.white,
            selectedFontSize: 16,
            currentIndex: context.read<MainHubCubit>().currentIndex,
            onTap: (index) {
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
                label: S.of(context).categories,
              ),
              BottomNavigationBarItem(
                activeIcon: Container(
                  width: 60,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(shape: BoxShape.rectangle, borderRadius: BorderRadius.circular(20), color: Constants.lightSecondaryColor),
                  child: SvgPicture.asset(
                    Assets.appIconsSelectedCart,
                  ),
                ),
                icon: SvgPicture.asset(Assets.appIconsCartIcon),
                label: S.of(context).cart,
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
