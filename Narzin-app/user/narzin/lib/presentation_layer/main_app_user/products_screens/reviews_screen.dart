import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:flutter_svg/svg.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/product_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:intl/intl.dart';
import 'package:narzin/model_layer/product_reviews_model.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../core/constants.dart';
import '../../../generated/l10n.dart';
import 'add_review_screen.dart';

class ReviewsScreen extends StatelessWidget {
  ReviewsScreen({super.key});

  late List<String> filters;
  late var localizedDate;

  @override
  Widget build(BuildContext context) {
    localizedDate = DateFormat('d MMMM yyyy', BlocProvider.of<LocalizationCubit>(context).locale);
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
        backgroundColor: Colors.white,
        leading: IconButton(
            onPressed: () {
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.arrow_back_ios_rounded)),
        title: Text(
          S.of(context).reviews,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
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
      body: BlocBuilder<ProductsCubit, ProductsState>(
        builder: (context, state) {
          filters = [S.of(context).status_all, S.of(context).positive, S.of(context).critical];
          bool isLoading = context.read<ProductsCubit>().isLoading;
          if(isLoading){
            return const Center(child: CircularProgressIndicator(),);
          }else{
            ProductReviewsModel? reviews = context.read<ProductsCubit>().productReviews;
            int selectedIndex = context.read<ProductsCubit>().selectedIndex;
            if(reviews == null|| (reviews.data?.isEmpty??true)){
              return const Center(child: Text('There is no reviews yet to that product'),);
            }else{
              var reviewsList = reviews.data;
              if(selectedIndex == 0){
                reviewsList = reviews.data;
              }else if (selectedIndex == 1){
                reviewsList = reviews.data!.where((element) => (double.tryParse(element.rating.toString()??'0')??0) > 2.5,).toList();
              }else{
                reviewsList = reviews.data!.where((element) => (double.tryParse(element.rating.toString()??'0')??0) < 2.5,).toList();

              }
              return SizedBox(
                height: ScreenSizing.height,
                width: ScreenSizing.width,
                child: Column(
                  children: [
                    const SizedBox(
                      height: 30,
                    ),
                    Container(
                      height: 40,
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      width: ScreenSizing.width,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemBuilder: (context, index) {
                          int selectedIndex = context.read<ProductsCubit>().selectedIndex;
                          return InkWell(
                            onTap: () {
                              context.read<ProductsCubit>().setSelectedIndex(index);
                            },
                            child: Container(
                              margin: const EdgeInsets.only(left: 7),
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
                              height: 40,
                              constraints: const BoxConstraints(minWidth: 100),
                              decoration: index == selectedIndex
                                  ? BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      Color(0xff3084C2),
                                      Color(0xff5BB5EF),
                                    ],
                                  ),
                                  borderRadius: BorderRadius.circular(30))
                                  : BoxDecoration(
                                borderRadius: BorderRadius.circular(30),
                                color: Constants.lighterSecondaryColor,
                                border: Border.all(
                                  color: Constants.lighterSecondaryColor,
                                ),
                              ),
                              child: Center(
                                child: Text(filters[index], style: index == selectedIndex ? const TextStyle(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600) : const TextStyle(fontSize: 17, color: Color(0xff5BB5EF))),
                              ),
                            ),
                          );
                        },
                        separatorBuilder: (context, index) => const SizedBox(
                          width: 0,
                        ),
                        itemCount: filters.length,
                      ),
                    ),
                    const SizedBox(
                      height: 30,
                    ),
                    Expanded(
                      child: ListView.separated(
                          itemBuilder: (context, index) {
                            return Container(
                              constraints: const BoxConstraints(minHeight: 100,),
                              width: ScreenSizing.width,
                              margin: const EdgeInsets.symmetric(
                                horizontal: 20,
                              ),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                color: Colors.grey[300],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Row(
                                    children: [
                                      const SizedBox(width: 5,),
                                      Expanded(
                                        child: Text(reviewsList?[index].user?.name??''),
                                      ),
                                      Text('${localizedDate.format(DateTime.tryParse(reviewsList?[index].createdAt??'')??DateTime.now())}')
                                    ],
                                  ),
                                  RatingBar.builder(
                                    itemSize: 15,
                                    initialRating: double.tryParse(reviewsList?[index].rating.toString()??'')??0,
                                    allowHalfRating: true,
                                    glow: true,
                                    ignoreGestures: true,
                                    itemCount: 5,
                                    itemPadding: const EdgeInsets.symmetric(horizontal: 5),
                                    itemBuilder: (context, index) {
                                      return SvgPicture.asset(Assets.appIconsRating);
                                    },
                                    onRatingUpdate: (double value) {
                                      //context.read<ProductsCategoriesCubit>().rate = value.toString();
                                    },
                                  ),
                                  const SizedBox(height: 10,),
                                  Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 5.0),
                                    child: Text(reviewsList?[index].review??''),
                                  )
                                ],
                              ),
                            );
                          },
                          separatorBuilder: (context, index) => const SizedBox(
                            height: 10,
                          ),
                          itemCount: reviewsList?.length??0),
                    ),
                  ],
                ),
              );
            }

          }

        },
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10.0),
        child: CustomSignIn_UpOne(title: S.of(context).add_review,ontap: () {
          BlocProvider.of<ProductsCubit>(context).resetReviewForm();
          Navigator.push(context, MaterialPageRoute(builder: (context) => const AddReviewScreen(),),);

        },),
      ),
    );
  }
}
