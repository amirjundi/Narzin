import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/merchant_cubits/merchant_auth_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/sign_in_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/profile_screens/about_us/vendor_about_us_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/profile_screens/merchant_update_screens/merchant_update_screen.dart';
import 'package:narzin/presentation_layer/main_app_vendor/profile_screens/settings_screens/vendor_settings_screen.dart';
import 'package:narzin/presentation_layer/onboarding_screens/sign_in_up_screen.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../../generated/l10n.dart';

class VendorProfileScreen extends StatelessWidget {
  const VendorProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: SingleChildScrollView(
            child: BlocBuilder<ProfileCubit, ProfileState>(
              builder: (context, state) {
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: [
                        CircleAvatar(
                          backgroundColor: Colors.grey[300],
                          radius: 33,
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(100),
                            child: SizedBox(
                              height: 65,
                              width: 65,
                              child: InstaNetworkImageWidget(imageUrl: 'https://admin.narzin.com/storage/${BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.storeLogo??''}',),
                            ),
                          ),
                        ),
                        Expanded(
                          child: ListTile(
                            minTileHeight: kToolbarHeight * 1.2,
                            title: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 5),
                              child: Text(
                                context.read<ProfileCubit>().profile?.data?.user?.name ?? 'Not available',
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                            ),
                            subtitle: const Row(
                              children: [
                                Icon(
                                  Icons.location_on_outlined,
                                  size: 20,
                                ),
                                Text(
                                  'مصر، القاهرة',
                                ),
                                Icon(Icons.keyboard_arrow_down),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(
                      height: 20,
                    ),
                    InkWell(
                        onTap: () {
                          context.read<ProfileCubit>().setControllers();
                          Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const VendorSettingsScreen(),
                              ));
                        },
                        child: ProfileTile(
                          image: Assets.appIconsSettings,
                          title: S.of(context).settings,
                        )),
                    const SizedBox(
                      height: 20,
                    ),
                    BlocBuilder<MerchantAuthCubit, MerchantAuthState>(
                      builder: (merchantContext, state) {
                        return InkWell(
                            onTap: () async {
                              merchantContext.read<MerchantAuthCubit>().prepareMerchantDetails(BlocProvider.of<LoginCubit>(context).vendorData!);
                              Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const MerchantUpdateScreen(),
                                  ));
                            },
                            child: ProfileTile(
                              image: Assets.appIconsStores,
                              title: S.of(context).update_merchant,
                            ));
                      },
                    ),
                    const SizedBox(
                      height: 20,
                    ),
                    ProfileTile(
                      image: Assets.appIconsCallUs,
                      title: S.of(context).contact_us,
                    ),
                    const SizedBox(
                      height: 20,
                    ),
                    InkWell(
                      onTap: () {
                        Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const VendorAboutUsScreen(),
                            ));
                      },
                      child: ProfileTile(
                        image: Assets.appIconsAboutus,
                        title: S.of(context).about_us,
                      ),
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
        InkWell(
          onTap: () async {
            SharedPreferences prefs = await SharedPreferences.getInstance();
            await prefs.clear();
            await const FlutterSecureStorage().deleteAll();
            BlocProvider.of<MainHubCubit>(context).setCurrentIndex(0);
            Navigator.popUntil(
              context,
              (route) => route.isFirst,
            );
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(
                builder: (context) => const SignInScreen(),
              ),
            );
          },
          child: ProfileTile(
            image: Assets.appIconsLogout,
            title: S.of(context).logout,
          ),
        ),
      ],
    );
  }
}

class ProfileTile extends StatelessWidget {
  const ProfileTile({
    super.key,
    required this.image,
    required this.title,
  });

  final String image;
  final String title;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 70,
      constraints: const BoxConstraints(minWidth: 216),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Image.asset(image),
          const SizedBox(
            width: 15,
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 17),
          ),
          const Spacer(),
          const Icon(Icons.arrow_forward_ios_rounded)
        ],
      ),
    );
  }
}
