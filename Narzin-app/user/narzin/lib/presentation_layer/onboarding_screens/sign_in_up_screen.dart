import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/sign_in_screen.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_up_user/sign_up_user_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/main_hub.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../generated/assets.dart';
import '../../generated/l10n.dart';

class SignInUpScreen extends StatelessWidget {
  const SignInUpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
            child: Stack(
              children: [
                Container(
                  margin: const EdgeInsets.only(left: 20, right: 20),
                  width: MediaQuery.of(context).size.width,
                  height: MediaQuery.of(context).size.height * 0.94,
                  child: Center(
                    child: SingleChildScrollView(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.start,
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Padding(
                            padding: EdgeInsets.only(top: MediaQuery.of(context).size.height * 0.02, bottom: MediaQuery.of(context).size.height * 0.05),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                SvgPicture.asset(
                                  Assets.imagesSplashIcon2,
                                  width: ScreenSizing.width,
                                  height: ScreenSizing.height * 0.32,
                                  fit: BoxFit.contain,
                                ),
                              ],
                            ),
                          ),
                          Padding(
                            padding: EdgeInsets.only(bottom: MediaQuery.of(context).size.height * 0.02),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.end,
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                CustomSignIn_UpOne(
                                  title: S.of(context).sign_in,
                                  ontap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => const SignInScreen(),
                                      ),
                                    );
                                  },
                                ),
                                CustomSignIn_UpThree(
                                  title: S.of(context).create_account,
                                  ontap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => const SignUpUserScreen(),
                                      ),
                                    );
                                  },
                                ),
                                Padding(
                                  padding: EdgeInsets.symmetric(horizontal: ScreenSizing.width*0.2),
                                  child: CustomSignIn_UpTwo(
                                    title: S.of(context).guest_login,
                                    fixedHeight: 40,
                                    color: Constants.lighterSecondaryColor,
                                    ontap: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => const MainHub(),
                                        ),
                                      );
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Column(
                            mainAxisAlignment: MainAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Expanded(
                                    flex: 1,
                                    child: Divider(
                                      height: 1,
                                      color: Color(0x88000000),
                                      thickness: 1,
                                      indent: 5,
                                      endIndent: 10,
                                    ),
                                  ),
                                  Expanded(
                                    child: Center(child: Text(S.of(context).or_with)),
                                  ),
                                  const Expanded(
                                    flex: 1,
                                    child: Divider(
                                      height: 1,
                                      color: Color(0x88000000),
                                      thickness: 1,
                                      indent: 10,
                                      endIndent: 5,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(
                                height: 20,
                              ),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.only(
                                      left: 10,
                                      right: 10,
                                    ),
                                    child: SizedBox(
                                      height: 32,
                                      width: 32,
                                      child: SvgPicture.asset(Assets.appIconsFacebook),
                                    ),
                                  ),
                                  Padding(
                                    padding: const EdgeInsets.only(
                                      left: 10,
                                      right: 10,
                                    ),
                                    child: SizedBox(
                                      height: 32,
                                      width: 32,
                                      child: SvgPicture.asset(Assets.appIconsGoogle),
                                    ),
                                  ),
                                  Padding(
                                    padding: const EdgeInsets.only(
                                      left: 10,
                                      right: 10,
                                    ),
                                    child: SizedBox(
                                      height: 32,
                                      width: 32,
                                      child: SvgPicture.asset(Assets.appIconsApple),
                                    ),
                                  ),
                                ],
                              ),
                              SizedBox(
                                height: MediaQuery.of(context).size.height * 0.05,
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                Positioned(
                  child: BlocBuilder<LocalizationCubit, LocalizationState>(
                    builder: (context, state) {
                      return IconButton(
                          onPressed: () {
                            context.read<LocalizationCubit>().changeLocale();
                          },
                          icon: const Icon(
                            Icons.language,
                            color: Colors.grey,
                          ));
                    },
                  ),
                )
              ],
            ),
          ),
    );
  }
}
