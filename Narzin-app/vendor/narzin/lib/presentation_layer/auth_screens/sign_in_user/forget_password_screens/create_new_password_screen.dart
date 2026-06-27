import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/forget_password_screens/new_password_affirmation_screen.dart';
import 'package:narzin/widgets/text_form_fields/custom_password_form_field.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

import '../../../../core/constants.dart';
import '../../../../generated/assets.dart';
import '../../../../generated/l10n.dart';
import '../../../../widgets/buttons/custom_main_buttons.dart';

class CreateNewPasswordScreen extends StatelessWidget {
  const CreateNewPasswordScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              mainAxisAlignment: MainAxisAlignment.start,
              children: [
                Row(
                  children: [
                    IconButton(
                      onPressed: () {
                        Navigator.canPop(context) ? Navigator.pop(context) : null;
                      },
                      icon: const Icon(Icons.arrow_back_ios_new_rounded,size: 20,),)
                  ],
                ),
                SvgPicture.asset(
                  Assets.imagesResetPasswordAsset,
                  height: ScreenSizing.height * 0.3,
                ),
                SizedBox(
                  height: 10,
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      S.of(context).create_new_password,
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      S.of(context).back_to_your_journey,
                      style: TextStyle(fontSize: 16, color: Color(0xB2000000)),
                    ),
                  ],
                ),
                SizedBox(
                  height: 10,
                ),
                BlocBuilder<LoginCubit, LoginState>(
                  builder: (context, state) {
                    bool isVisible = context.read<LoginCubit>().isVisible;
                    return CustomPasswordFormField(
                      controller: context.read<LoginCubit>().password,
                      title: S.of(context).password,
                      hint: S.of(context).enter_your_password,
                      isVisible: isVisible,
                      onTap: context.read<LoginCubit>().toggleIsVisible,
                    );
                  },
                ),
                Text(
                  S.of(context).password_guidelines,
                  style: TextStyle(fontSize: 12, color: Constants.grey),
                ),
                SizedBox(
                  height: 10,
                ),
                BlocBuilder<LoginCubit, LoginState>(
                  builder: (context, state) {
                    bool isVisible = context.read<LoginCubit>().isVisible;
                    return CustomPasswordFormField(
                      controller: context.read<LoginCubit>().password,
                      title: S.of(context).confirm_password,
                      hint: S.of(context).reenter_password,
                      isVisible: isVisible,
                      onTap: context.read<LoginCubit>().toggleIsVisible,
                    );
                  },
                ),


              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 65,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: CustomSignIn_UpOne(
            title: S.of(context).reset_password,
            ontap: () {
              Navigator.popUntil(context, (route) =>  route.isFirst,);
              Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => NewPasswordAffirmationScreen(),
                  ));
            },
          ),
        ),
      ),
    );
  }
}
