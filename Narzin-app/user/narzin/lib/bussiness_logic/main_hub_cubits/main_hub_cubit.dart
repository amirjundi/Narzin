import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/presentation_layer/main_app_user/cart_screens/cart_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/categories_screens/categories_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/home_screens/home_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/profile_screen.dart';

part 'main_hub_state.dart';

class MainHubCubit extends Cubit<MainHubState> {
  MainHubCubit() : super(MainHubInitial());

  int currentIndex = 0;

  List<Widget> mainAppScreens = [
    SafeArea(
      child: Container(
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 15,vertical: 10),
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(child: HomeScreen()),
          ],
        ),
      ),
    ),
    const CategoriesScreen(),
    const CartScreen(),
    SafeArea(
      child: Container(
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 15,vertical: 10),
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(child: ProfileScreen()),
          ],
        ),
      ),
    ),
  ];

  setCurrentIndex(int index){
    currentIndex = index;
    emit(MainHubInitial());
  }
}
