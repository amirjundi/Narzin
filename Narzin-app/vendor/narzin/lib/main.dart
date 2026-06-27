import 'package:animated_splash_screen/animated_splash_screen.dart';
import 'package:device_preview/device_preview.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:narzin/bussiness_logic/connectivity_cubits/connectivity_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/bussiness_logic/merchant_cubits/merchant_auth_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/bussiness_logic/register_cubits/register_cubit.dart';
import 'package:narzin/bussiness_logic/vendor_stats_cubits/vendor_stats_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/presentation_layer/auth_screens/sign_in_user/sign_in_screen.dart';
import 'package:narzin/presentation_layer/onboarding_screens/onboarding_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'bussiness_logic/localization_cubit/localization_cubit.dart';
import 'bussiness_logic/onboarding_cubit/on_boarding_cubit.dart';
import 'core/screen_sizing_constants.dart';
import 'generated/l10n.dart';
import 'presentation_layer/main_app_vendor/vendor_main_hub.dart';

import 'dart:io';
import 'package:http/http.dart' as http;

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

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = MyHttpOverrides();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);
  runApp(DevicePreview(
      enabled: false,
      builder: (context) {
        return MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => OnBoardingCubit(),
            ),
            BlocProvider(
              create: (context) => ProductCubit(),
            ),
            BlocProvider(
              create: (context) => MerchantAuthCubit(),
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
              create: (context) => ProductManipulationCubit(),
            ),
            BlocProvider(
              create: (context) => VendorStatsCubit(),
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
        // print("hello");
        if (state is ConnectivityListen) {
          Fluttertoast.showToast(msg: 'Internet Connection restored!!', backgroundColor: Colors.greenAccent, textColor: Colors.white);
        } else if (state is ConnectivityFailed) {
          // print("hello");
          Fluttertoast.showToast(msg: 'No internet Connection\n\"Please open your wifi or your data and wait for reloading\"', backgroundColor: Color(0xffD0021B), textColor: Colors.white);
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
                    await BlocProvider.of<ProfileCubit>(context).getProfile(token: context.read<LoginCubit>().vendorData?.data?.token);
                    return const VendorMainHub();
                    // return const MainHub();
                  } else {
                    return SignInScreen();
                  }
                } else {
                  return SignInScreen();
                }
              },
            );
          },
        ),
      ),
    );
  }
}
