import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/forget_password_screens/forget_password_screen.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_up_user/sign_up_user_screen.dart';
import 'package:narzin/widgets/auth_specific_widget/header_builder.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

import '../../../core/constants.dart';
import '../../../generated/l10n.dart';
import '../../../widgets/text_form_fields/custom_password_form_field.dart';
import '../../main_app_vendor/vendor_main_hub.dart';

class SignInScreen extends StatelessWidget {
  const SignInScreen({super.key});

  static final _key = GlobalKey<FormState>();

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
                  const SizedBox(
                    height: 20,
                  ),
                  HeaderBuilder(
                    headerText: S.of(context).welcome_back_with,
                    pageTitle: S.of(context).sign_in,
                    confirmTitle: S.of(context).register_new_account,
                    askTitle: S.of(context).dont_have_account,
                    onTap: () {
                      Navigator.pushReplacement(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const SignUpUserScreen(),
                          ));
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<LoginCubit, LoginState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<LoginCubit>().email,
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
                  BlocBuilder<LoginCubit, LoginState>(
                    builder: (context, state) {
                      bool isVisible = context.read<LoginCubit>().isVisible;
                      return CustomPasswordFormField(
                        controller: context.read<LoginCubit>().password,
                        title: S.of(context).password,
                        hint: S.of(context).enter_your_password,
                        isVisible: isVisible,
                        onTap: context.read<LoginCubit>().toggleIsVisible,
                        validator: (p0) {
                          return validatePassword(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  Row(
                    children: [
                      Expanded(
                        child: BlocBuilder<LoginCubit, LoginState>(
                          builder: (context, state) {
                            bool rememberMe = context.read<LoginCubit>().rememberMe;
                            return CheckboxListTile(
                              value: rememberMe,
                              checkColor: Colors.black,
                              activeColor: Colors.transparent,
                              dense: true,
                              checkboxShape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(5), side: const BorderSide(color: Colors.black)),
                              controlAffinity: ListTileControlAffinity.leading,
                              contentPadding: EdgeInsets.zero,
                              title: Text(S.of(context).remember_me),
                              onChanged: (val) => context.read<LoginCubit>().toggleRememberMe(val ?? false),
                            );
                          },
                        ),
                      ),
                      TextButton(
                          style: TextButton.styleFrom(
                            padding: EdgeInsets.zero,
                          ),
                          onPressed: () {
                            Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ForgetPasswordScreen(),
                                ));
                          },
                          child: Column(
                            children: [
                              Text(
                                S.of(context).forgot_password,
                              ),
                              Divider(
                                color: Constants.mainColor,
                                height: 4,
                                thickness: 1,
                                endIndent: 1,
                                indent: 1,
                              ),
                            ],
                          ))
                    ],
                  ),
                  SizedBox(
                    height: 65,
                    child: BlocBuilder<LoginCubit, LoginState>(
                      builder: (context, state) {
                        bool isLoading = context.read<LoginCubit>().isLoading;
                        return CustomSignIn_UpOne(
                          title: S.of(context).sign_in,
                          customizeChild: isLoading
                              ? const Center(
                                  child: CircularProgressIndicator(
                                    color: Colors.white,
                                  ),
                                )
                              : Text(
                                  S.of(context).sign_in,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                ),
                          ontap: isLoading
                              ? null
                              : () async {
                                  if (_key.currentState!.validate()) {
                                    var res = await context.read<LoginCubit>().vendorLogin();
                                    var res2 = await BlocProvider.of<ProfileCubit>(context).getProfile(token: context.read<LoginCubit>().vendorData?.data?.token);
                                    if(res == null && res2 == null){
                                      Navigator.popUntil(context, (route) => route.isFirst,);
                                      Navigator.pushReplacement(
                                        context,
                                        MaterialPageRoute(
                                          // builder: (context) => MainHub(),
                                          builder: (context) => const VendorMainHub(),
                                        ),
                                      );
                                    }

                                  }
                                },
                        );
                      },
                    ),
                  )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
