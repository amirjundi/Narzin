import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/sign_in_screen.dart';

import '../../../../core/screen_sizing_constants.dart';
import '../../../../generated/assets.dart';
import '../../../../generated/l10n.dart';
import '../../../../widgets/buttons/custom_main_buttons.dart';

class NewPasswordAffirmationScreen extends StatelessWidget {
  const NewPasswordAffirmationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Row(
                children: [
                  IconButton(
                    onPressed: () {
                      Navigator.canPop(context) ? Navigator.pop(context) : null;
                    },
                    icon: const Icon(
                      Icons.arrow_back_ios_new_rounded,
                      size: 20,
                    ),
                  )
                ],
              ),
              const Spacer(),
              SvgPicture.asset(
                Assets.imagesNewPasswordAfirmationAsset,
                height: ScreenSizing.height * 0.35,
              ),
              const SizedBox(
                height: 20,
              ),
              Text(
                S.of(context).password_changed_successfully,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              Text(
                S.of(context).password_changed_success_message,
                style: const TextStyle(
                  fontSize: 14,
                  color: Color(0xB2000000),
                ),
                textAlign: TextAlign.center,
              ),

              const Spacer(
                flex: 2,
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 65,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: CustomSignIn_UpOne(
            title: S.of(context).back_to_sign_in,
            ontap: () {
              Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (context) => SignInScreen(),
                  ));
            },
          ),
        ),
      ),
    );
  }
}
