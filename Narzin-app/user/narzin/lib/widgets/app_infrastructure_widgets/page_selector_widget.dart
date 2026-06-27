import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

class PageSelector extends StatelessWidget {
  const PageSelector({
    super.key,
    required int currentPage,
    required int totalPages,
    required this.onTap,
    this.onNextPressed,
    this.onPreviousPressed,
  })  : _currentPage = currentPage,
        _totalPages = totalPages;

  final int _currentPage;
  final int _totalPages;
  final void Function()? onNextPressed;
  final void Function()? onPreviousPressed;
  final void Function(int) onTap;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        // Previous button
        Container(
          height: 40,
          width: 50,
          padding: const EdgeInsets.symmetric(horizontal: 5),
          child: CustomSignIn_UpOne(
            contentPadding: EdgeInsets.zero,
            title: '',
            customizeChild: const Center(
                child: Icon(
                  Icons.arrow_back_ios_rounded,
                  color: Colors.white,
                )),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(100),
            ),
            padding: EdgeInsets.zero,
            ontap: _currentPage > 0 ?onPreviousPressed : null,
          ),
        ),
        // Page numbers
        Expanded(
          child: Center(
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center, // Center the items
                children: [
                  ...List.generate(
                    _totalPages,
                        (index) {
                      // Show first page, last page, current page, and adjacent pages
                      if (index == 0 || index == _totalPages - 1 || index == _currentPage || (index == _currentPage - 1) || (index == _currentPage + 1)) {
                        return GestureDetector(
                          onTap:() {
                            if(index == _currentPage) return;
                            onTap(index+1);
                          } ,
                          child: NumberItem(
                            currentPage: _currentPage,
                            index: index,
                          ),
                        );
                      }

                      // Add ellipses if necessary
                      if (index == _currentPage - 2 || index == _currentPage + 2 || (index == 1 && _currentPage > 3) || (index == _totalPages - 2 && _currentPage < _totalPages - 4)) {
                        return const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Text(
                            '....',
                            style: TextStyle(fontSize: 14, color: Colors.grey),
                          ),
                        );
                      }
                      return const SizedBox.shrink(); // Skip unnecessary numbers
                    },
                  ),
                ],
              ),
            ),
          ),
        ),

        Container(
          height: 40,
          width: 50,
          padding: const EdgeInsets.symmetric(horizontal: 5),
          child: CustomSignIn_UpOne(
            contentPadding: EdgeInsets.zero,
            title: '',
            customizeChild: const Center(
                child: Icon(
                  Icons.arrow_forward_ios_rounded,
                  color: Colors.white,
                )),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(100),
            ),
            padding: EdgeInsets.zero,
            ontap: _currentPage < _totalPages - 1 ? onNextPressed : null,
          ),
        ),
      ],
    );
  }
}

class NumberItem extends StatelessWidget {
  const NumberItem({super.key, required int currentPage, required this.index}) : _currentPage = currentPage;

  final int _currentPage;
  final int index;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: index == _currentPage ? null : Colors.grey[200],
        gradient: index == _currentPage
            ? const LinearGradient(colors: [
          Color(0xff5BB5EF),
          Color(0xff3084C2),
        ])
            : null,
        borderRadius: BorderRadius.circular(8),
        boxShadow: index == _currentPage
            ? [
          BoxShadow(
            color: Constants.lightSecondaryColor,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ]
            : [],
      ),
      child: Text(
        '${index + 1}',
        style: TextStyle(
          color: index == _currentPage ? Colors.white : Colors.black,
          fontWeight: index == _currentPage ? FontWeight.bold : FontWeight.normal,
          fontSize: 14,
        ),
      ),
    );
  }
}