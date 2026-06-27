import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:narzin/core/constants.dart';

import '../../generated/assets.dart';

class HeaderBuilder extends StatelessWidget {
  const HeaderBuilder({
    super.key,
    required this.headerText,
    required this.pageTitle,
    required this.askTitle,
    required this.confirmTitle,
    required this.onTap,
  });

  final String headerText;
  final String pageTitle;
  final String askTitle;
  final String confirmTitle;
  final void Function()? onTap;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisAlignment: MainAxisAlignment.start,
      children: [
        Text(
          headerText,
          style: const TextStyle(
            fontSize: 20,
            color: Colors.black,
            fontWeight: FontWeight.w300,
          ),
        ),
        Row(
          children: [
            SvgPicture.asset(Assets.appIconsInappLogo),
          ],
        ),
        const SizedBox(
          height: kToolbarHeight*0.7,
        ),
        Text(
          pageTitle,
          style: const TextStyle(
            fontSize: 20,
            color: Colors.black,
            fontWeight: FontWeight.w600,
          ),
        ),
        // const SizedBox(
        //   height: 10,
        // ),
        // Row(
        //   crossAxisAlignment: CrossAxisAlignment.start,
        //   children: [
        //     Text(
        //       '${askTitle}  ',
        //       style: const TextStyle(
        //         fontSize: 15,
        //         color: Color(0xbf0D0E0E),
        //         // fontWeight: FontWeight.w300,
        //       ),
        //     ),
        //     Expanded(
        //       child: InkWell(
        //         onTap: onTap,
        //         child: Text(
        //           '${confirmTitle}',
        //           style: TextStyle(
        //             fontSize: 15,
        //             color: Constants.mainColor,
        //             fontWeight: FontWeight.w600,
        //           ),
        //         ),
        //       ),
        //     ),
        //   ],
        // )
      ],
    );
  }
}