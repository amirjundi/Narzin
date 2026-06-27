import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';

class UnselectedSizeWidget extends StatelessWidget {
  const UnselectedSizeWidget({
    super.key,
    required this.value,
  });

  final String? value;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 55,
      padding: const EdgeInsets.all(2),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(5),
        border: Border.all(
          color: Colors.grey,
        ),
      ),
      child: Container(
        height: 35,
        constraints: const BoxConstraints(minWidth: 50),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(5),
        ),
        child: Center(
            child: Text(
          value ?? 'X',
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 22),
        )),
      ),
    );
  }
}

class SelectedSizeWidget extends StatelessWidget {
  const SelectedSizeWidget({
    super.key,
    required this.value,
  });

  final String? value;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 55,
      padding: const EdgeInsets.all(2),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(5),
        gradient: const LinearGradient(
          colors: [
            Color(0xff5BB5EF),
            Color(0xff3084C2),
          ],
        ),
      ),
      child: Container(
        height: 55,
        constraints: const BoxConstraints(minWidth: 55),
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(4), color: Colors.white),
        child: Center(
            child: Text(
          (value ?? 'X'),
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 22),
        )),
      ),
    );
  }
}

class UnselectedColorWidget extends StatelessWidget {
  const UnselectedColorWidget({super.key, this.colorsAttrs, required this.index});

  final String? colorsAttrs;
  final int index;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 40,
      width: 40,
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(200), boxShadow: [
        BoxShadow(color: Colors.grey[100]!, blurRadius: 2, spreadRadius: 1, offset: const Offset(1, 2)),
      ]),
      child: Container(
        height: 35,
        width: 35,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(200),
          color: Color(int.tryParse(colorsAttrs ?? '${0xff111111 * index}') ?? 0xff7777777 * index),
          image: (colorsAttrs?.contains('/') ?? false)
              ? DecorationImage(
                  image: NetworkImage("${colorsAttrs ?? ''}"),
                  fit: BoxFit.cover,
                )
              : null,
        ),
      ),
    );
  }
}

class SelectedColorWidget extends StatelessWidget {
  const SelectedColorWidget({super.key, this.colorsAttrs, required this.index});

  final String? colorsAttrs;
  final int index;

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        const SizedBox(
          height: 60,
          width: 55,
        ),
        Container(
          height: 40,
          width: 40,
          padding: const EdgeInsets.all(5),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(200),
            border: Border.all(color: Constants.mainColor),
            boxShadow: [
              BoxShadow(
                color: Colors.grey[100]!,
                blurRadius: 2,
                spreadRadius: 1,
                offset: const Offset(1, 2),
              ),
            ],
          ),
          child: Container(
            height: 35,
            width: 35,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(200),
              color: Color(int.tryParse(colorsAttrs ?? '${0xff111111 * index}') ?? 0xff7777777 * index),
              image: (colorsAttrs?.contains('/') ?? false)
                  ? DecorationImage(
                image: NetworkImage("${colorsAttrs ?? ''}"),
                fit: BoxFit.cover,
              )
                  : null,
            ),
          ),
        ),
        Positioned(
          top: 0,
          left: 0,
          child: Container(
            width: 22,
            height: 22,
            padding: const EdgeInsets.all(2),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(100),
            ),
            child: Center(
              child: Icon(
                Icons.check_circle_rounded,
                size: 20,
                color: Constants.mainColor,
              ),
            ),
          ),
        )
      ],
    );
  }
}
