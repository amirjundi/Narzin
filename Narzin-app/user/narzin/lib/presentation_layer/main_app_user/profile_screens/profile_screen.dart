import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/bussiness_logic/wallet_cubits/wallet_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/about_us/about_us_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/cards_screens/cards_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/merchant_request_screens/merchant_request_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/my_orders_screen/my_orders_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/my_orders_screen/returns_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/settings_screens/settings_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/wallet_screens/wallet_screen.dart';
import 'package:narzin/presentation_layer/main_app_user/profile_screens/wishlist_screens/wishlist_screen.dart';
import 'package:narzin/presentation_layer/onboarding_screens/sign_in_up_screen.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/screen_sizing_constants.dart';
import '../../../generated/l10n.dart';
import '../../../model_layer/addresses_model.dart';
import '../../../widgets/image_widgets/insta_image_widget.dart';
import '../address_screens/add_address_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});
  showAddressesMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      barrierColor: const Color(0x1F000000),
      backgroundColor: Colors.white,
      constraints: BoxConstraints(maxHeight: ScreenSizing.height * 0.6, minHeight: ScreenSizing.height * 0.4, minWidth: ScreenSizing.width),
      sheetAnimationStyle: AnimationStyle(
        duration: const Duration(milliseconds: 300),
      ),
      builder: (context) {
        return BlocBuilder<ProfileCubit, ProfileState>(
          builder: (context, state) {
            bool isLoading = context.read<ProfileCubit>().isLoading;
            List<AddressData> addresses = context.read<ProfileCubit>().addressesModel?.data ?? [];
            return Container(
              padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        S.of(context).set_delivery_location,
                        style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
                      ),
                      IconButton(
                          onPressed: () {
                            Navigator.canPop(context) ? Navigator.pop(context) : null;
                          },
                          icon: const Icon(Icons.close))
                    ],
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  Expanded(
                    child: addresses.isEmpty
                        ? Center(
                        child: Text(
                          S.of(context).no_saved_addresses,
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: Colors.grey[400]),
                        ))
                        : ListView.separated(
                        itemBuilder: (context, index) {
                          return InkWell(
                            onTap: () {
                              String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                              context.read<ProfileCubit>().setSelectedGroup(addresses[index].id.toString(), addresses[index].title ?? '');
                              context.read<ProfileCubit>().setIsAddressesDefault(token: token, id: addresses[index].id.toString());
                            },
                            child: Container(
                              constraints: const BoxConstraints(minHeight: 70),
                              width: ScreenSizing.width,
                              decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), color: Colors.grey[200]),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: ListTile(
                                      title: Text(addresses[index].address ?? 'Not available'),
                                      subtitle: Text('منزل$index'),
                                    ),
                                  ),
                                  Radio<String?>(
                                    onChanged: (value) {
                                      context.read<ProfileCubit>().setSelectedGroup(value ?? '', addresses[index].title ?? '');
                                    },
                                    value: addresses[index].id.toString(),
                                    groupValue: context.read<ProfileCubit>().selectedAddress,
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                        separatorBuilder: (context, index) => const SizedBox(
                          height: 10,
                        ),
                        itemCount: addresses.length),
                  ),
                  const SizedBox(
                    height: 10,
                  ),
                  InkWell(
                    onTap: isLoading
                        ? null
                        : () async {
                      context.read<ProfileCubit>().resetEveryThing();
                      // await context.read<ProfileCubit>().getCoordinates();
                      String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                      var res = await context.read<ProfileCubit>().getDeliveryZones(token: token);
                      if (res == null) {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const AddAddressScreen(),
                          ),
                        );
                      }
                    },
                    child: Container(
                      height: 50,
                      width: ScreenSizing.width,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.grey[300]!),
                      ),
                      child: isLoading
                          ? const Center(
                        child: CircularProgressIndicator(),
                      )
                          : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.add,
                            color: Colors.grey[500]!,
                          ),
                          Text(
                            S.of(context).add,
                            style: TextStyle(color: Colors.grey[500]!, fontSize: 18),
                          )
                        ],
                      ),
                    ),
                  )
                ],
              ),
            );
          },
        );
      },
    );
  }
  @override
  Widget build(BuildContext context) {
    return BlocProvider.of<LoginCubit>(context).loginModel == null? Center(child: Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(S.of(context).inaccessible_view,style: TextStyle(color: Constants.mainColor,fontSize: 20,fontWeight: FontWeight.w500),textAlign: TextAlign.center,),
        const SizedBox(height: 30,),
        CustomSignIn_UpTwo(title: S.of(context).register,ontap: () async {
          SharedPreferences prefs = await SharedPreferences.getInstance();
          await prefs.clear();
          BlocProvider.of<MainHubCubit>(context).setCurrentIndex(0);
          BlocProvider.of<LoginCubit>(context).resetEverything();
          Helpers.wishlistItems = {};
          Navigator.popUntil(
            context,
                (route) => route.isFirst,
          );
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => const SignInUpScreen(),
            ),
          );
        },)
      ],
    ),):
    SingleChildScrollView(
      child: BlocBuilder<ProfileCubit, ProfileState>(
        builder: (context, state) {
          return  Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return Row(
                    mainAxisAlignment: MainAxisAlignment.start,
                    children: [
                      CircleAvatar(
                        backgroundColor: Colors.grey[300],
                        radius: 25,
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(100),
                          // child: const SizedBox(
                          //   height: 49,
                          //   width: 49,
                          //   child: InstaNetworkImageWidget(
                          //     imageUrl: 'https://astrotechsol.com/narzin-app-test/public/storage',
                          //   ),
                          // ),
                        ),
                      ),
                      Expanded(
                        child: ListTile(
                          onTap: () {
                            showAddressesMenu(context);
                          },
                          contentPadding: EdgeInsets.zero,
                          minTileHeight: kToolbarHeight * 1.2,
                          title: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 5.0),
                            child: Row(
                              children: [
                                Text(
                                  "${S.of(context).delivery_to} ",
                                  style: TextStyle(fontSize: 13, color: Colors.grey[700], fontWeight: FontWeight.w400),
                                ),
                              ],
                            ),
                          ),
                          subtitle: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 5.0),
                            child: Row(
                              children: [
                                Icon(
                                  Icons.wrong_location_outlined,
                                  size: 17,
                                  color: Colors.grey[600]!,
                                ),
                                Container(
                                  constraints: BoxConstraints(maxWidth: ScreenSizing.width * 0.4),
                                  child: Text(
                                    (context.read<ProfileCubit>().showAddress ?? ''),
                                    style: TextStyle(fontSize: 15, color: Colors.grey[600]!, fontWeight: FontWeight.w500),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const SizedBox(
                                  width: 5,
                                ),
                                SvgPicture.asset(Assets.appIconsArrowDown),
                              ],
                            ),
                          ),
                          trailing: IconButton(
                            style: IconButton.styleFrom(
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10), side: BorderSide(color: Colors.grey[300]!)),
                            ),
                            onPressed: () {},
                            icon: const Icon(
                              Icons.notifications_active_outlined,
                              size: 20,
                            ),
                          ),
                        ),
                      ),
                    ],
                  );
                },
              ),
              const SizedBox(
                height: 20,
              ),
              Row(
                children: [
                  Expanded(
                    child: BlocBuilder<OrderCubit, OrderState>(
                      builder: (orderContext, state) {
                        return InkWell(
                          onTap: () async {
                            String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                            orderContext.read<OrderCubit>().getMyOrder(token: token);
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const MyOrdersScreen(),
                              ),
                            );
                          },
                          child: Container(
                            height: 100,
                            padding: const EdgeInsets.only(bottom: 10),
                            decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
                            constraints: const BoxConstraints(minWidth: 50),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Expanded(
                                  child: Image.asset(
                                    Assets.appIconsOrders,
                                    fit: BoxFit.contain,
                                    height: 50,
                                    width: 50,
                                  ),
                                ),
                                Text(S.of(context).orders)
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(
                    width: 10,
                  ),
                  Expanded(
                    child: BlocBuilder<OrderCubit, OrderState>(
                      builder: (orderContext, state) {
                        return InkWell(
                          onTap: () {
                            String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                            orderContext.read<OrderCubit>().getMyOrder(token: token);
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const ReturnsScreen(),
                              ),
                            );
                          },
                          child: Container(
                            height: 100,
                            padding: const EdgeInsets.only(bottom: 10),
                            decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
                            constraints: const BoxConstraints(minWidth: 50),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Expanded(
                                    child: Image.asset(
                                  Assets.appIconsReturns,
                                  fit: BoxFit.contain,
                                  height: 50,
                                  width: 50,
                                )),
                                Text(S.of(context).returns)
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(
                    width: 10,
                  ),
                  Expanded(
                    child: InkWell(
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (context) => const CardsScreen(),));
                        // Navigator.push(context, MaterialPageRoute(builder: (context) => MerchantSuccessScreen(),));
                      },
                      child: Container(
                        height: 100,
                        padding: const EdgeInsets.only(bottom: 10),
                        decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
                        constraints: const BoxConstraints(minWidth: 50),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Expanded(
                                child: Image.asset(
                              Assets.appIconsCards,
                              fit: BoxFit.contain,
                              height: 50,
                              width: 50,
                            )),
                            Text(S.of(context).cards)
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(
                height: 20,
              ),
              BlocBuilder<WalletCubit, WalletState>(
                builder: (walletContext, state) {
                  return InkWell(
                    onTap: () {
                      String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                      walletContext.read<WalletCubit>().getWallet(
                            token: token,
                          );
                      walletContext.read<WalletCubit>().getWalletTransactions(
                        token: token,
                      );
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const WalletScreen(),
                        ),
                      );
                    },
                    child: ProfileTile(
                      image: Assets.appIconsWallet,
                      title: S.of(context).wallet,
                    ),
                  );
                },
              ),
              const SizedBox(
                height: 20,
              ),
              InkWell(
                onTap: () {
                  String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                  BlocProvider.of<ProductsCubit>(context).getWishlist(token: token);
                  Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const WishlistScreen(),
                      ));
                },
                child: ProfileTile(
                  image: Assets.appIconsHeart,
                  title: S.of(context).favorites,
                ),
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
                          builder: (context) => const SettingsScreen(),
                        ));
                  },
                  child: ProfileTile(
                    image: Assets.appIconsSettings,
                    title: S.of(context).settings,
                  )),
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
                        builder: (context) => const AboutUsScreen(),
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
              InkWell(
                onTap: () {
                  Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const MerchantRequestScreen(),
                      ));
                },
                child: ProfileTile(
                  image: Assets.appIconsStores,
                  title: S.of(context).register_as_merchant,
                ),
              ),
              const SizedBox(
                height: 20,
              ),
              InkWell(
                onTap: () async {
                  SharedPreferences prefs = await SharedPreferences.getInstance();
                  await prefs.clear();
                  BlocProvider.of<MainHubCubit>(context).setCurrentIndex(0);
                  Helpers.wishlistItems.clear();
                  BlocProvider.of<LoginCubit>(context).resetEverything();
                  Navigator.popUntil(
                    context,
                    (route) => route.isFirst,
                  );
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const SignInUpScreen(),
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
        },
      ),
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
