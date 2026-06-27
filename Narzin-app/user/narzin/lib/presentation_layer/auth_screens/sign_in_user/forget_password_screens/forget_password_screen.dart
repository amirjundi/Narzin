import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/forget_password_screens/email_sent_screen.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../../generated/assets.dart';
import '../../../../generated/l10n.dart';
import '../../../../widgets/text_form_fields/custom_text_form_field.dart';

class ForgetPasswordScreen extends StatelessWidget {
  const ForgetPasswordScreen({super.key});

  static final _key = GlobalKey<FormState>();

  // imagesForgetPasswordAsset
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: SingleChildScrollView(
            child: Form(
              key: _key,
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
                        icon: const Icon(
                          Icons.arrow_back_ios_new_rounded,
                          size: 20,
                        ),
                      )
                    ],
                  ),
                  SvgPicture.asset(
                    Assets.imagesForgetPasswordAsset,
                    height: ScreenSizing.height * 0.35,
                  ),
                  const SizedBox(
                    height: 30,
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        S.of(context).forgot_password_title,
                        style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      Text(
                        S.of(context).forgot_password_description,
                        style: const TextStyle(fontSize: 16, color: Color(0xB2000000)),
                      ),
                    ],
                  ),
                  const SizedBox(
                    height: 30,
                  ),
                  BlocBuilder<LoginCubit, LoginState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<LoginCubit>().email,
                        title: S.of(context).email,
                        hint: S.of(context).enter_your_email,
                        validator: (p0) {
                          validateEmail(p0);
                          return null;
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  Row(
                    children: [
                      Text(
                        '${S.of(context).remember_password}  ',
                        style: const TextStyle(
                          fontSize: 15,
                          color: Color(0xbf0D0E0E),
                          // fontWeight: FontWeight.w300,
                        ),
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.canPop(context) ? Navigator.pop(context) : null;
                        },
                        child: Text(
                          S.of(context).sign_in,
                          style: TextStyle(
                            fontSize: 15,
                            color: Constants.mainColor,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 65,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: BlocBuilder<LoginCubit, LoginState>(
            builder: (context, state) {
              bool isLoading = context.read<LoginCubit>().isLoading;
              return CustomSignIn_UpOne(
                customizeChild: isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                        color: Colors.white,
                      ))
                    : Text(
                        S.of(context).send_code,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                title: S.of(context).send_code,
                ontap: isLoading
                    ? null
                    : () async {
                        if (_key.currentState!.validate()) {
                          var res = await context.read<LoginCubit>().forgetPassword();
                          if (res == null) {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const EmailSentScreen(),
                              ),
                            );
                          }
                        }
                      },
              );
            },
          ),
        ),
      ),
    );
  }
}
