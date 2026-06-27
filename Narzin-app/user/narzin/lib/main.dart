import 'dart:io';

import 'package:animated_splash_screen/animated_splash_screen.dart';
import 'package:device_preview/device_preview.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:narzin/bussiness_logic/Banners_cubits/banners_cubit.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/connectivity_cubits/connectivity_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/merchant_cubits/merchant_auth_cubit.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/search_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/bussiness_logic/register_cubits/register_cubit.dart';
import 'package:narzin/bussiness_logic/wallet_cubits/wallet_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/main_app_user/main_hub.dart';
import 'package:narzin/presentation_layer/onboarding_screens/onboarding_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'bussiness_logic/localization_cubit/localization_cubit.dart';
import 'bussiness_logic/onboarding_cubit/on_boarding_cubit.dart';
import 'bussiness_logic/product_cubits/product_cubit.dart';
import 'core/screen_sizing_constants.dart';
import 'generated/l10n.dart';

class MyHttpOverrides extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    final client = super.createHttpClient(context);
    if (kDebugMode) {
      client.badCertificateCallback = (X509Certificate cert, String host, int port) => true;
    }
    return client;
  }
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // await Hive.initFlutter();
  HttpOverrides.global = MyHttpOverrides();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);

  runApp(DevicePreview(
      enabled: false,
      builder: (context) {
        ScreenSizing.init(context);
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => OnBoardingCubit(),
            ),
            BlocProvider(
              create: (context) => BannersCubit(),
            ),
            BlocProvider(
              create: (context) => WalletCubit(),
            ),
            BlocProvider(
              create: (context) => OrderCubit(),
            ),
            BlocProvider(
              create: (context) => MerchantAuthCubit(),
            ),
            BlocProvider(
              create: (context) => ProductsCubit(),
            ),
            BlocProvider(
              create: (context) => MainHubCubit(),
            ),
            BlocProvider(
              create: (context) => LocalizationCubit()..getLocale(),
            ),
            BlocProvider(
              create: (context) => ConnectivityCubit()..connectivityListener(),
              lazy: false,
            ),
            BlocProvider(
              create: (context) => RegisterCubit(),
            ),
            BlocProvider(
              create: (context) => LoginCubit(),
            ),
            BlocProvider(
              create: (context) => ProfileCubit(),
            ),
            BlocProvider(
              create: (context) => SearchCubit(),
            ),
            BlocProvider(
              create: (context) => CartCubit(),
            ),
          ],
          child: const MyApp(),
        );
      }));
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    ScreenSizing.init(context);
    return BlocConsumer<ConnectivityCubit, ConnectivityState>(
      listener: (context, state) {
        print("hello");
        if (state is ConnectivityListen) {
          Fluttertoast.showToast(msg: 'Internet Connection restored!!', backgroundColor: Colors.greenAccent, textColor: Colors.white);
        } else if (state is ConnectivityFailed) {
          print("hello");
          Fluttertoast.showToast(msg: 'No internet Connection\n"Please open your wifi or your data and wait for reloading"', backgroundColor: const Color(0xffD0021B), textColor: Colors.white);
        }
      },
      builder: (context, state) {
        return BlocBuilder<LocalizationCubit, LocalizationState>(
          builder: (localeContext, state) {
            return MaterialApp(
              useInheritedMediaQuery: true,
              builder: DevicePreview.appBuilder,
              locale: Locale(localeContext.read<LocalizationCubit>().locale),
              localizationsDelegates: const [
                S.delegate,
                GlobalMaterialLocalizations.delegate,
                GlobalWidgetsLocalizations.delegate,
                GlobalCupertinoLocalizations.delegate,
              ],
              supportedLocales: S.delegate.supportedLocales,
              debugShowCheckedModeBanner: false,
              title: 'Flutter Demo',
              theme: ThemeData(
                scaffoldBackgroundColor: Colors.white,
                // fontFamily: 'tajawal',
                colorScheme: ColorScheme.fromSeed(seedColor: Constants.mainColor),
                useMaterial3: true,
              ),
              home: const SplashScreen(),
              // home: TestScreen(),
            );
          },
        );
      },
    );
  }
}

class MainSplashWidget extends StatelessWidget {
  const MainSplashWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SizedBox(
          width: ScreenSizing.width,
          height: ScreenSizing.height,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Spacer(),
              SvgPicture.asset(
                Assets.imagesSplashIcon2,
                width: MediaQuery.of(context).size.width,
                height: MediaQuery.of(context).size.height * 0.3,
              ),
              const SizedBox(
                height: 20,
              ),
              Text(
                S.of(context).ready_wherever_you_are,
                style: TextStyle(color: Constants.secondaryColor, fontSize: 20, fontWeight: FontWeight.w400),
                textAlign: TextAlign.center,
              ),
              const Spacer(
                flex: 2,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SizedBox(
        height: MediaQuery.of(context).size.height,
        width: MediaQuery.of(context).size.width,
        child: BlocBuilder<LoginCubit, LoginState>(
          builder: (context, state) {
            return AnimatedSplashScreen.withScreenFunction(
              splash: const MainSplashWidget(),
              splashIconSize: double.infinity,
              screenFunction: () async {
                SharedPreferences prefs = await SharedPreferences.getInstance();
                bool rememberMe = prefs.getBool('rememberMe') ?? false;
                if (rememberMe) {
                  await context.read<LoginCubit>().handleRememberMe();
                  if (context.read<LoginCubit>().isRememberMeSucceeded) {
                    // BlocProvider.of<ProductsCubit>(context).getAllProducts();
                    // BlocProvider.of<ProductsCubit>(context).getCategories();
                    String token = context.read<LoginCubit>().loginModel?.data?.token??'';
                    await BlocProvider.of<ProfileCubit>(context).getProfile(token:token);
                    await BlocProvider.of<ProductsCubit>(context).getWishlist(token: token);
                    // BlocProvider.of<ProfileCubit>(context).getAddresses(token: token);
                    // return const VendorMainHub();
                    return const MainHub();
                  } else {
                    return const OnboardingScreen();
}
                } else {
                  return const OnboardingScreen();
                }
              },
            );
          },
        ),
      ),
    );
  }
}
