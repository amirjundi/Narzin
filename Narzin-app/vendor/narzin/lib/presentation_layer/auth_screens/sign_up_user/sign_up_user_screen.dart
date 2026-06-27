import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/register_cubits/register_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/sign_in_screen.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_up_user/verify_email_address_screen.dart';
import 'package:narzin/widgets/auth_specific_widget/header_builder.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../core/screen_sizing_constants.dart';
import '../../../generated/l10n.dart';
import '../../../widgets/text_form_fields/custom_password_form_field.dart';
import '../../../widgets/text_form_fields/custom_text_form_field.dart';

class SignUpUserScreen extends StatelessWidget {
  const SignUpUserScreen({super.key});
  static final _key = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.only(left: 20,right: 20, top: 10),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: SingleChildScrollView(
            child: Form(
              key: _key,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  const SizedBox(
                    height: 20,
                  ),
                  HeaderBuilder(
                    headerText: S.of(context).start_your_journey_with,
                    pageTitle: S.of(context).create_new_user_account,
                    askTitle: S.of(context).already_have_account,
                    confirmTitle: S.of(context).sign_in,
                    onTap: () {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const SignInScreen(),
                        ),
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<RegisterCubit, RegisterState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<RegisterCubit>().name,
                        title: S.of(context).full_name,
                        hint: S.of(context).enter_your_full_name,
                        validator: (p0) {
                          return validateName(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<RegisterCubit, RegisterState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<RegisterCubit>().email,
                        title: S.of(context).email,
                        hint: S.of(context).enter_your_email,
                        validator: (p0) {
                          return validateEmail(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      BlocBuilder<RegisterCubit, RegisterState>(
                        builder: (context, state) {
                          bool isVisible = context.read<RegisterCubit>().isVisible;
                          return CustomPasswordFormField(
                            controller: context.read<RegisterCubit>().password,
                            title: S.of(context).password,
                            hint: S.of(context).enter_your_password,
                            isVisible: isVisible,
                            onTap: context.read<RegisterCubit>().toggleIsVisible,
                            validator: (p0) {
                              return validatePassword(p0);
                            },
                          );
                        },
                      ),
                      Text(
                        S.of(context).password_guidelines,
                        style: TextStyle(fontSize: 12, color: Constants.grey),
                      )
                    ],
                  ),
                  const SizedBox(
                    height: 7,
                  ),
                  BlocBuilder<RegisterCubit, RegisterState>(
                    builder: (context, state) {
                      bool isVisible = context.read<RegisterCubit>().isVisible;
                      return CustomPasswordFormField(
                        controller: context.read<RegisterCubit>().confirmPassword,
                        title: S.of(context).confirm_password,
                        hint: S.of(context).reenter_password,
                        isVisible: isVisible,
                        onTap: context.read<RegisterCubit>().toggleIsVisible,
                        validator: (p0) {
                          if(context.read<RegisterCubit>().confirmPassword.text.isEmpty){
                            return 'Password field is empty';
                          }else if (context.read<RegisterCubit>().confirmPassword.text != context.read<RegisterCubit>().password.text){
                            return 'Password doesn\'t match';

                          }
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),

                ],
              ),
            ),
          ),
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 65,
        child: BlocBuilder<RegisterCubit, RegisterState>(
          builder: (context, state) {
            bool isLoading = context.read<RegisterCubit>().isLoading;
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: CustomSignIn_UpOne(
                customizeChild: isLoading
                    ? const Center(
                  child: CircularProgressIndicator(
                    color: Colors.white,
                  ),
                )
                    : Text(
                  S.of(context).register,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                title: S.of(context).register,
                ontap: isLoading? null: () async {
                  if (_key.currentState!.validate()) {
                    var res = await context.read<RegisterCubit>().register();
                    if(res == null){
                      Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => VerifyEmailAddressScreen(),));
                    }
                  }
                  // context.read<RegisterCubit>()
                },
              ),
            );
          },
        ),
      ),
    );
  }
}
