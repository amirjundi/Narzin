import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/bussiness_logic/vendor_stats_cubits/vendor_stats_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/presentation_layer/main_app_vendor/profile_screens/settings_screens/password_change_screens/vendor_password_change_screen.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field_profile.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../../../bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import '../../../../bussiness_logic/merchant_cubits/merchant_auth_cubit.dart';
import '../../../../widgets/text_form_fields/custom_password_form_field_profile.dart';
import '../../../onboarding_screens/onboarding_screen.dart';

class VendorSettingsScreen extends StatelessWidget {
  const VendorSettingsScreen({super.key});

  static final _key = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).settings,
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
                  child: SingleChildScrollView(
                    child: Form(
                      key: _key,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const SizedBox(
                            height: 20,
                          ),
                          Container(
                            width: ScreenSizing.width,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                            decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey[200]!)),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Text(
                                  S.of(context).account,
                                  style: const TextStyle(
                                    fontSize: 17,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Center(
                                  child: Stack(
                                    children: [
                                      const SizedBox(
                                        width: 140,
                                        child: CircleAvatar(
                                          radius: 60,
                                          backgroundImage: NetworkImage('https://s3-alpha-sig.figma.com/img/abf7/ce1e/6eec8209b3e82758eaf1cfd7a74b8fd9?Expires=1734307200&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=aCnlluGfEDCGKnkDHCqIKwyHT~~xmrsCA7du5ZzvA2AOy202XEr6JyUpqSthpdYTPO1AMMi8vgayrWb2vB~WvpfUJADH3S~azbZfoVPPCXfLbyZGNk2cTEss4ypUIwCeHXRJ6OIetljdFQNWCYNp-oAy7xWpCpkxmfN8DKi~Rd6s0xaekoNe7IUbrNb9LVjUE1gEicSKwkAl54kyFSPq2VHiiVkuoGfK7SynX8gTMI0tCvHdI1GdEM5CXmjc5Jj6I5PTfmlFgTtTgtqNK5JkVfHyKa9RZGtQMJOh5I~lFPBE6R7Klnw7r2f9AJMP3FIkAF2VfaBLX6Sy1kwyw~IoOw__'),
                                        ),
                                      ),
                                      Positioned(
                                        bottom: 0,
                                        child: IconButton(
                                          style: IconButton.styleFrom(backgroundColor: Constants.lighterSecondaryColor),
                                          onPressed: () {},
                                          icon: const Icon(Icons.edit),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(
                                  height: 20,
                                ),
                                ProfileCustomTextFormField(
                                  controller: context.read<ProfileCubit>().name,
                                  title: S.of(context).full_name,
                                  hint: S.of(context).enter_your_full_name,
                                  editTitle: S.of(context).edit,
                                  isActive: !context.read<ProfileCubit>().isNameEditable,
                                  onTap: () {
                                    context.read<ProfileCubit>().setIsNameEditable();
                                  },
                                  validator: (p0) {
                                    return validateName(p0);
                                  },
                                ),
                                const SizedBox(
                                  height: 20,
                                ),
                                ProfileCustomTextFormField(
                                  controller: context.read<ProfileCubit>().email,
                                  title: S.of(context).email,
                                  hint: S.of(context).enter_your_email,
                                  editTitle: S.of(context).edit,
                                  isActive: !context.read<ProfileCubit>().isEmailEditable,
                                  onTap: () {
                                    context.read<ProfileCubit>().setIsEmailEditable();
                                  },
                                  validator: (p0) {
                                    return validateEmail(p0);
                                  },
                                ),
                                const SizedBox(
                                  height: 20,
                                ),
                                Divider(
                                  color: Colors.grey[200],
                                  thickness: 2,
                                ),
                                const SizedBox(
                                  height: 20,
                                ),
                                ProfileCustomPasswordFormField(
                                  title: S.of(context).password,
                                  hint: '******************',
                                  editTitle: S.of(context).edit,
                                  isActive: true,
                                  isVisible: false,
                                  onTap: () {},
                                  onPressed: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => const VendorPasswordChangeScreen(),
                                      ),
                                    );
                                  },
                                ),
                                const SizedBox(
                                  height: 40,
                                ),
                                SizedBox(
                                  height: 60,
                                  child: CustomSignIn_UpOne(
                                    title: S.of(context).save,
                                    color: const Color(0xff848484),
                                    customizeChild: context.read<ProfileCubit>().isLoading
                                        ? const Center(
                                            child: CircularProgressIndicator(
                                              color: Colors.white,
                                            ),
                                          )
                                        : Text(
                                            S.of(context).save,
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
                                              var res = await context.read<ProfileCubit>().updateProfile(choice: 1, token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '');
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
                                    title: S.of(context).cancel,
                                    color: const Color(0xffEEEEEE),
                                    textColor: const Color(0xff848484),
                                    ontap: () {
                                      context.read<ProfileCubit>().setControllers();
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(
                            height: 20,
                          ),
                          Container(
                            width: ScreenSizing.width,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.grey[200]!),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.language),
                                    const SizedBox(
                                      width: 5,
                                    ),
                                    Text(
                                      S.of(context).language,
                                      style: const TextStyle(
                                        fontSize: 17,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                                BlocBuilder<LocalizationCubit, LocalizationState>(
                                  builder: (LocaleContext, state) {
                                    return RadioListTile<String>(
                                      contentPadding: EdgeInsets.zero,
                                      controlAffinity: ListTileControlAffinity.trailing,
                                      secondary: Text(
                                        S.of(context).arabic,
                                        style: const TextStyle(fontSize: 16),
                                      ),
                                      value: LocaleContext.read<LocalizationCubit>().locale,
                                      groupValue: 'ar',
                                      onChanged: (value) {
                                        BlocProvider.of<VendorStatsCubit>(context).setSelectedValue(null);
                                        BlocProvider.of<ProductManipulationCubit>(context).setSelectedCategory(null);
                                        LocaleContext.read<LocalizationCubit>().setLang('ar');
                                      },
                                    );
                                  },
                                ),
                                BlocBuilder<LocalizationCubit, LocalizationState>(
                                  builder: (LocaleContext, state) {
                                    return RadioListTile<String>(
                                      contentPadding: EdgeInsets.zero,
                                      controlAffinity: ListTileControlAffinity.trailing,
                                      secondary: Text(
                                        S.of(context).english,
                                        style: const TextStyle(fontSize: 16),
                                      ),
                                      value: LocaleContext.read<LocalizationCubit>().locale,
                                      groupValue: 'de',
                                      onChanged: (value) {
                                        BlocProvider.of<VendorStatsCubit>(context).setSelectedValue(null);
                                        BlocProvider.of<ProductManipulationCubit>(context).setSelectedCategory(null);
                                        LocaleContext.read<LocalizationCubit>().setLang('de');
                                      },
                                    );
                                  },
                                ),
                                const Divider(
                                  indent: 5,
                                  endIndent: 10,
                                ),
                                const SizedBox(
                                  height: 10,
                                ),
                                Row(
                                  children: [
                                    const Icon(Icons.notifications_none_outlined),
                                    Text(S.of(context).notification),
                                    const Spacer(),
                                    CupertinoSwitch(
                                      activeColor: Constants.mainColor,
                                      trackColor: Colors.grey[300],
                                      value: context.read<ProfileCubit>().notificationEnabled,
                                      onChanged: (bool value) {
                                        context.read<ProfileCubit>().toggleNotificationEnabled();
                                      },
                                    )
                                  ],
                                )
                              ],
                            ),
                          ),
                          const SizedBox(
                            height: 20,
                          ),
                          Container(
                            width: ScreenSizing.width,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.grey[200]!),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Text(
                                  S.of(context).delete_account,
                                  style: const TextStyle(
                                    fontSize: 17,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  S.of(context).delete_account_message,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    color: Colors.grey,
                                    fontWeight: FontWeight.normal,
                                  ),
                                ),
                                const SizedBox(
                                  height: 15,
                                ),
                                SizedBox(
                                  height: 60,
                                  child: BlocBuilder<MerchantAuthCubit, MerchantAuthState>(
                                    builder: (merchantContext, state) {
                                      bool isLoading = merchantContext.read<MerchantAuthCubit>().isLoading;
                                      return CustomSignIn_UpOne(
                                        title: S.of(context).delete_account_button,
                                        customizeChild: isLoading? Center(child: CircularProgressIndicator(color: Colors.red[800],),): Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.restore_from_trash_outlined,
                                              color: Colors.red[800],
                                            ),
                                            Text(
                                              S.of(context).delete_merchant,
                                              style: TextStyle(
                                                fontSize: 16,
                                                fontWeight: FontWeight.w500,
                                                color: Colors.red[800]!,
                                              ),
                                            ),
                                          ],
                                        ),
                                        color: const Color(0xffFDDFDF),
                                        ontap: () async {
                                         var res =await merchantContext.read<MerchantAuthCubit>().deleteMerchant(
                                                token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',
                                                id: BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.id?.toString() ?? '0',
                                              );
                                         if(res == null){
                                           SharedPreferences prefs = await SharedPreferences.getInstance();
                                           await prefs.clear();
                                           await const FlutterSecureStorage().deleteAll();
                                           BlocProvider.of<MainHubCubit>(context).setCurrentIndex(0);
                                           Navigator.popUntil(context, (route) => route.isFirst,);
                                           Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => OnboardingScreen(),),);
                                         }

                                        },
                                      );
                                    },
                                  ),
                                )
                              ],
                            ),
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
    );
  }
}
