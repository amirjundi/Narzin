import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_password_form_field.dart';

import '../../../../../bussiness_logic/profile_cubits/profile_cubit.dart';

class VendorPasswordChangeScreen extends StatelessWidget {
  const VendorPasswordChangeScreen({super.key});

  static final _key = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).change_password,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        actions: [
          IconButton(
            onPressed: () {
              // Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.more_vert_sharp),
          ),
        ],
        centerTitle: true,
      ),
      body: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        width: ScreenSizing.width,
        child: BlocBuilder<ProfileCubit, ProfileState>(
          builder: (context, state) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Expanded(
                  child: Form(
                    key: _key,
                    child: SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          SizedBox(
                            height: 10,
                          ),
                          CustomPasswordFormField(
                            controller: context.read<ProfileCubit>().currentPassword,
                            title: S.of(context).old_password,
                            hint: S.of(context).enter_your_password,
                            isVisible: context.read<ProfileCubit>().isVisible,
                            onTap: () {
                              context.read<ProfileCubit>().setIsVisible();
                            },
                            validator: (p0) {
                              return validatePassword(p0);
                            },
                          ),
                          SizedBox(
                            height: 10,
                          ),
                          CustomPasswordFormField(
                              controller: context.read<ProfileCubit>().password,
                            title: S.of(context).new_password,
                            hint: S.of(context).enter_your_password,
                            isVisible: context.read<ProfileCubit>().isVisible,
                            onTap: () {
                              context.read<ProfileCubit>().setIsVisible();
                            },
                            validator: (p0) {
                              return validatePassword(p0);
                            },
                          ),
                          SizedBox(
                            height: 10,
                          ),
                          CustomPasswordFormField(
                              controller: context.read<ProfileCubit>().confirmPassword,
                            title: S.of(context).confirm_password,
                            hint: S.of(context).enter_your_password,
                            isVisible: context.read<ProfileCubit>().isVisible,
                            onTap: () {
                              context.read<ProfileCubit>().setIsVisible();
                            },
                            validator: (p0) {
                              if(p0?.isEmpty??true){
                                return 'please enter password confirmation';
                              }
                              else if(p0 != context.read<ProfileCubit>().password.text){
                                return 'Password confirmation does\'t match new password.';
                              } else{
                                return null;
                              }
                            },
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              height: 60,
              child: CustomSignIn_UpOne(
                title: S.of(context).save,
                customizeChild: context.read<ProfileCubit>().isLoading
                    ? const Center(
                  child: CircularProgressIndicator(
                    color: Colors.white,
                  ),
                )
                    : Text(
                  S.of(context).save_changes,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                ontap: context.read<ProfileCubit>().isLoading
                    ? null
                    : () async {
                  if (_key.currentState!.validate()) {
                    var res = await context.read<ProfileCubit>().updateProfile(choice: 0, token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '');
                    if (res == null) {
                      context.read<ProfileCubit>().setControllers();
                    }
                  }
                },
              ),
            ),
            SizedBox(
              height: 40,
              child: CustomSignIn_UpThree(
                title: S.of(context).cancel_changes,
                ontap: () {
                  context.read<ProfileCubit>().setControllers();
                  Navigator.canPop(context)?Navigator.pop(context):null;
                },
              ),
            ),
            SizedBox(
              height: 30,
            ),
          ],
        ),
      ),
    );
  }
}
