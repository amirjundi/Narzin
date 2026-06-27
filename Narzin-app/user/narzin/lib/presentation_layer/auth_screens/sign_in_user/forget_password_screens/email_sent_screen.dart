import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';

import '../../../../core/constants.dart';
import '../../../../generated/l10n.dart';

class EmailSentScreen extends StatefulWidget {
  const EmailSentScreen({super.key});

  @override
  State<EmailSentScreen> createState() => _EmailSentScreenState();
}

class _EmailSentScreenState extends State<EmailSentScreen> {
  // @override
  // void initState() {
  //   Future.delayed(const Duration(seconds: 10)).then(
  //     (value) {
  //       Navigator.popUntil(context, (route) => route.isFirst,);
  //     },
  //   );
  //   super.initState();
  // }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: Column(
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
              const Spacer(),
              SvgPicture.asset(
                Assets.imagesEmailSentAsset,
                height: ScreenSizing.height * 0.35,
              ),
              const SizedBox(
                height: 20,
              ),
              Text(
                S.of(context).success,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              Text(
                S.of(context).check_email_reset_password,
                style: const TextStyle(
                  fontSize: 14,
                  color: Color(0xB2000000),
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(
                height: 20,
              ),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    '${S.of(context).didnt_get_code}  ',
                    style: const TextStyle(
                      fontSize: 15,
                      color: Color(0xbf0D0E0E),
                      // fontWeight: FontWeight.w300,
                    ),
                  ),
                  Expanded(
                    child: BlocBuilder<LoginCubit, LoginState>(
                      builder: (context, state) {
                        bool isLoading = context.read<LoginCubit>().isLoading;
                        return InkWell(
                          onTap: isLoading
                              ? null
                              : () async {
                                  showDialog(
                                    context: context,
                                    builder: (context) => const Center(child: CircularProgressIndicator()),
                                    barrierDismissible: false,
                                  );
                                  var res = await context.read<LoginCubit>().forgetPassword();
                                  Navigator.canPop(context)?Navigator.pop(context):null;
                                },
                          child: Text(
                            S.of(context).resend_code,
                            style: TextStyle(
                              fontSize: 15,
                              color: Constants.mainColor,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ],
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
