import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_decorated_container/flutter_decorated_container.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/merchant_cubits/merchant_auth_cubit.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

import '../../../../bussiness_logic/profile_cubits/profile_cubit.dart';
import '../../../../core/screen_sizing_constants.dart';
import '../../../../generated/assets.dart';
import 'merchant_success_screen.dart';

class MerchantUpdateScreen extends StatelessWidget {
  const MerchantUpdateScreen({super.key});

  static final _key = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: BlocBuilder<MerchantAuthCubit, MerchantAuthState>(
          builder: (context, state) {
            File? storeLogo = context.read<MerchantAuthCubit>().storeLogo;
            File? storeId = context.read<MerchantAuthCubit>().storeId;
            return Container(
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
                        pageTitle: S.of(context).update_merchant,
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      CustomTextFormField(
                        controller: context.read<MerchantAuthCubit>().phone,
                        title: S.of(context).phone,
                        hint: S.of(context).phone_hint,
                        validator: (p0) {
                          return validatePhoneNumber(p0);
                        },
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      CustomTextFormField(
                        controller: context.read<MerchantAuthCubit>().address,
                        title: S.of(context).address,
                        hint: S.of(context).address_hint,
                        validator: (p0) {
                          if (p0?.isEmpty ?? true) {
                            return 'this field shouldn\'t be empty.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      CustomTextFormField(
                        controller: context.read<MerchantAuthCubit>().category,
                        title: S.of(context).category,
                        hint: S.of(context).choose_category,
                        validator: (p0) {
                          if (p0?.isEmpty ?? true) {
                            return 'this field shouldn\'t be empty.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      Row(
                        children: [
                          Expanded(
                            child: CustomTextFormField(
                              controller: context.read<MerchantAuthCubit>().arabicName,
                              title: S.of(context).store_name_ar,
                              hint: S.of(context).store_name_ar,
                              validator: (p0) {
                                if (p0?.isEmpty ?? true) {
                                  return 'this field shouldn\'t be empty.';
                                }
                                return null;
                              },
                            ),
                          ),
                          const SizedBox(
                            width: 15,
                          ),
                          Expanded(
                            child: CustomTextFormField(
                              controller: context.read<MerchantAuthCubit>().englishName,
                              title: S.of(context).store_name_en,
                              hint: S.of(context).store_name_en,
                              validator: (p0) {
                                if (p0?.isEmpty ?? true) {
                                  return 'this field shouldn\'t be empty.';
                                }
                                return null;
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      CustomDescFormField(
                        controller: context.read<MerchantAuthCubit>().description,
                        title: S.of(context).description,
                        hint: S.of(context).write_store_description,
                        validator: (p0) {
                          if (p0?.isEmpty ?? true) {
                            return 'this field shouldn\'t be empty.';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      Text(
                        S.of(context).store_image,
                        style: const TextStyle(fontSize: 16),
                      ),
                      const SizedBox(
                        height: 5,
                      ),
                      InkWell(
                        onTap: () async {
                          await context.read<MerchantAuthCubit>().pickImageFromGallery(choise: 0);
                        },
                        child: DecoratedContainer(
                          strokeWidth: 1,
                          dashSpace: 4,
                          dashWidth: 6,
                          cornerRadius: 16,
                          strokeColor: Colors.grey,
                          child: Container(
                            width: ScreenSizing.width,
                            height: 100,
                            child: storeLogo != null
                                ? ClipRRect(
                                    borderRadius: BorderRadius.circular(10),
                                    child: Image.file(
                                      storeLogo,
                                      fit: BoxFit.cover,
                                    ))
                                : Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    crossAxisAlignment: CrossAxisAlignment.center,
                                    children: [
                                      const Icon(
                                        Icons.perm_media_outlined,
                                        size: 20,
                                      ),
                                      const SizedBox(
                                        width: 10,
                                      ),
                                      Text(S.of(context).store_image_placeholder),
                                    ],
                                  ),
                          ),
                        ),
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                      Text(
                        S.of(context).store_id,
                        style: const TextStyle(fontSize: 16),
                      ),
                      const SizedBox(
                        height: 5,
                      ),
                      InkWell(
                        onTap: () async {
                          await context.read<MerchantAuthCubit>().pickImageFromGallery(choise: 1);
                        },
                        child: DecoratedContainer(
                          strokeWidth: 1,
                          dashSpace: 4,
                          dashWidth: 6,
                          cornerRadius: 16,
                          strokeColor: Colors.grey,
                          child: Container(
                            width: ScreenSizing.width,
                            height: 100,
                            child: storeId != null
                                ? ClipRRect(
                                    borderRadius: BorderRadius.circular(10),
                                    child: Image.file(
                                      storeId,
                                      fit: BoxFit.cover,
                                    ))
                                : Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    crossAxisAlignment: CrossAxisAlignment.center,
                                    children: [
                                      const Icon(
                                        Icons.perm_media_outlined,
                                        size: 20,
                                      ),
                                      const SizedBox(
                                        width: 10,
                                      ),
                                      Text(S.of(context).store_id_placeholder),
                                    ],
                                  ),
                          ),
                        ),
                      ),
                      const SizedBox(
                        height: 20,
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
      bottomNavigationBar: SizedBox(
          height: 65,
          child: BlocBuilder<MerchantAuthCubit, MerchantAuthState>(
            builder: (context, state) {
              bool isLoading = context.read<MerchantAuthCubit>().isLoading;
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: CustomSignIn_UpOne(
                  title: S.of(context).send,
                  customizeChild: isLoading
                      ? const Center(
                          child: CircularProgressIndicator(
                            color: Colors.white,
                          ),
                        )
                      : Text(
                          S.of(context).send,
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
                            var res = await context.read<MerchantAuthCubit>().updateMerchant(
                                  token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',
                                  id: BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.id.toString() ?? '',
                                );

                            if (res == null) {
                              var res3 = await BlocProvider.of<LoginCubit>(context).handleRememberMe();
                              var res2 = await BlocProvider.of<ProfileCubit>(context).getProfile(token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '');
                              if(res3 == null && res2 == null){
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const MerchantSuccessScreen(),
                                  ),
                                );
                              }

                            }
                          }
                        },
                ),
              );
            },
          )),
    );
  }
}

class HeaderBuilder extends StatelessWidget {
  const HeaderBuilder({
    super.key,
    required this.pageTitle,
  });

  final String pageTitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisAlignment: MainAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SvgPicture.asset(Assets.appIconsInappLogo),
          ],
        ),
        const SizedBox(
          height: kToolbarHeight * 0.7,
        ),
        Text(
          pageTitle,
          style: const TextStyle(
            fontSize: 20,
            color: Colors.black,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}
