import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';

import '../../generated/assets.dart';

part 'on_boarding_state.dart';

class OnBoardingCubit extends Cubit<OnBoardingState> {
  OnBoardingCubit() : super(OnBoardingInitial());
  final PageController pageController = PageController();
  int numberOfScreens = 3;
  int selectedScreen = 0;
  List<String> images = [
    Assets.imagesFirstOnboarding1,
    Assets.imagesSecondOnBoarding1,
    Assets.imagesThirdOnboarding1,
  ];

  void goToNextScreen() {
    selectedScreen = (selectedScreen + 1) % numberOfScreens;
    emit(OnBoardingInitial());// Loop through 3 screens
    pageController.animateToPage(
      selectedScreen,
      duration: Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  void changeCurrentPageIndex(int index){
    selectedScreen = index;
    emit(OnBoardingInitial());
  }


}
