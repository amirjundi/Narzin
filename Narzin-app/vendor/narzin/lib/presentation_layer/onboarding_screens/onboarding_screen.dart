import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/onboarding_cubit/on_boarding_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/onboarding_screens/sign_in_up_screen.dart';

import '../../core/screen_sizing_constants.dart';
import '../../generated/l10n.dart';

class OnboardingScreen extends StatelessWidget {
  OnboardingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          margin: const EdgeInsets.symmetric(horizontal: 20),
          child: BlocBuilder<OnBoardingCubit, OnBoardingState>(
            builder: (context, state) {
              int numberOfItems = context.read<OnBoardingCubit>().numberOfScreens;
              int selectedItem = context.read<OnBoardingCubit>().selectedScreen;
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Expanded(
                    child: PageView(
                      onPageChanged: (index) {
                        context.read<OnBoardingCubit>().changeCurrentPageIndex(index);
                      },
                      controller: context.read<OnBoardingCubit>().pageController,
                      physics: const AlwaysScrollableScrollPhysics(), // Disable manual swipe
                      children: [
                        OnboardingBodyBuilder(
                          image: Assets.imagesFirstOnboarding1,
                          text: S.of(context).narzin_your_style_your_way,
                          subText: S.of(context).discover_fashion_expressing_you,
                        ),
                        OnboardingBodyBuilder(
                          image: Assets.imagesSecondOnBoarding1,
                          text: S.of(context).elevate_your_closet,
                          subText: S.of(context).step_into_trendy_fashion,
                        ),
                        OnboardingBodyBuilder(
                          image: Assets.imagesThirdOnboarding1,
                          text: S.of(context).more_than_fashion,
                          subText: S.of(context).shop_all_you_need,
                        ),
                      ],
                    ),
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Stack(
                        alignment: Alignment.center,
                        fit: StackFit.loose,
                        children: [
                          SizedBox(
                              height: 55,
                              width: 55,
                              child: CircularProgressIndicator(
                                backgroundColor: Colors.grey[200],
                                value: (selectedItem + 1) / numberOfItems,
                              )),
                          ElevatedButton(
                            onPressed: () {
                              if (selectedItem == numberOfItems - 1) {
                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const SignInUpScreen(),
                                  ),
                                );
                              }
                              context.read<OnBoardingCubit>().goToNextScreen();
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Constants.mainColor,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(100)),
                              maximumSize: const Size(40, 40),
                              minimumSize: const Size(40, 40),
                              padding: EdgeInsets.zero,
                            ),
                            child: const Icon(
                              Icons.arrow_back_ios_new_rounded,
                              color: Colors.white,
                              size: 15,
                            ),
                          )
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Row(
                            children: [
                              for (int i = 0; i < 3; i++)
                                AnimatedContainer(
                                  margin: const EdgeInsets.symmetric(horizontal: 3),
                                  width: i == selectedItem ? 28 : 12,
                                  height: 12,
                                  duration: const Duration(milliseconds: 300),
                                  decoration: BoxDecoration(
                                    color: i == selectedItem ? Constants.mainColor : Constants.notSelectedPoint,
                                    borderRadius: BorderRadius.circular(200),
                                  ),
                                ),
                            ],
                          ),
                          TextButton(
                              onPressed: () {
                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const SignInUpScreen(),
                                  ),
                                );
                              },
                              child: Text(
                                S.of(context).skip,
                              ))
                        ],
                      )
                    ],
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }
}

class OnboardingBodyBuilder extends StatelessWidget {
  OnboardingBodyBuilder({
    super.key,
    required this.image,
    required this.text,
    required this.subText,
  });

  String image;
  String text;
  String subText;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SizedBox(
          height: ScreenSizing.height * 0.1,
        ),
        Image.asset(
          image,
        ),
        const Spacer(),
        Text(
          text,
          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w500),
        ),
        Text(
          subText,
          style: const TextStyle(fontSize: 15, color: Color(0xff4B5563)),
        ),
        const Spacer(),
      ],
    );
  }
}
