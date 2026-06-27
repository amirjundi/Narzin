import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';
import 'package:narzin/presentation_layer/main_app_vendor/orders_screens/orders_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/products_screens/products_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/profile_screens/vendor_profile_screen.dart';

import '../../presentation_layer/main_app_vendor/vendor_home_screens/vendor_home_screen.dart';

part 'main_hub_state.dart';

class MainHubCubit extends Cubit<MainHubState> {
  MainHubCubit() : super(MainHubInitial());

  int currentIndex = 0;

  List<Widget> vendorMainAppScreens = [
    VendorHomeScreen(),
    ProductsScreen(),
    OrdersScreen(),
    VendorProfileScreen(),
  ];

  setCurrentIndex(int index){
    currentIndex = index;
    emit(MainHubInitial());
  }
}
