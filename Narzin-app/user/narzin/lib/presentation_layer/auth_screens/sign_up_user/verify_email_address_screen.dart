import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/register_cubits/register_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:timer_count_down/timer_controller.dart';
import 'package:timer_count_down/timer_count_down.dart';

import '../../../generated/l10n.dart';
import '../../../widgets/buttons/custom_main_buttons.dart';

class VerifyEmailAddressScreen extends StatelessWidget {
  VerifyEmailAddressScreen({super.key});

  final CountdownController countdownController = CountdownController(autoStart: true);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
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
                BlocBuilder<RegisterCubit, RegisterState>(
                  builder: (context, state) {
                    String email = context.read<RegisterCubit>().email.text;
                    print(email);
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          S.of(context).email_confirmation,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          S.of(context).send_email_confirmation_link,
                          style: const TextStyle(fontSize: 14, color: Color(0xB2000000)),
                        ),
                        Text(
                          email.isNotEmpty ? email : 'email@example.email',
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                      ],
                    );
                  },
                ),
                SvgPicture.asset(
                  Assets.imagesEmailVerficationAsset,
                  height: ScreenSizing.height * 0.35,
                ),
                const SizedBox(
                  height: 20,
                ),
                Row(
                  children: [
                    Text(
                      '${S.of(context).didnt_receive_email}  ',
                      style: const TextStyle(
                        fontSize: 15,
                        color: Color(0xbf0D0E0E),
                        // fontWeight: FontWeight.w300,
                      ),
                    ),
                    BlocBuilder<RegisterCubit, RegisterState>(
                      builder: (context, state) {
                        return InkWell(
                          onTap: context.read<RegisterCubit>().isResend
                              ? () {
                                  countdownController.restart();
                                  context.read<RegisterCubit>().resendVerification();
                                  context.read<RegisterCubit>().setIsResendFalse();

                                }
                              : null,
                          child: Text(
                            S.of(context).resend_code,
                            style: TextStyle(
                              fontSize: 15,
                              color: context.read<RegisterCubit>().isResend ? Constants.mainColor : Constants.grey,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        );
                      },
                    ),
                    const Spacer(),
                    Countdown(
                      controller: countdownController,
                      seconds: 60,
                      build: (BuildContext context, double time) => Text('00:${time.round()}'),
                      interval: const Duration(milliseconds: 100),
                      onFinished: () {
                        print('Timer is done!');
                        BlocProvider.of<RegisterCubit>(context).setIsResendTrue();
                      },
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 65,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: CustomSignIn_UpOne(title: S.of(context).confirm, ontap: () {
            Navigator.canPop(context)?Navigator.pop(context):null;
          }),
        ),
      ),
    );
  }
}
