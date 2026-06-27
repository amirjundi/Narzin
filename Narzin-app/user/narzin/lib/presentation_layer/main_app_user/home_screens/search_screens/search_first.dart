import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/cart_cubits/cart_cubit.dart';
import 'package:narzin/bussiness_logic/product_cubits/search_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/presentation_layer/main_app_user/home_screens/search_screens/searchSecond.dart';

import '../../../../generated/l10n.dart';
import '../../cart_screens/cart_screen.dart';

class SearchFirst extends StatefulWidget {
  const SearchFirst({super.key});

  @override
  State<SearchFirst> createState() => _SearchFirstState();
}

class _SearchFirstState extends State<SearchFirst> {

  @override
  void initState() {
    // TODO: implement initState
    BlocProvider.of<SearchCubit>(context).getSearchSuggestions();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        BlocProvider.of<SearchCubit>(context).resetFilters();
        return true;
      },
      child: Scaffold(
        appBar: AppBar(
          toolbarHeight: kToolbarHeight * 1.1,
          bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
          backgroundColor: Colors.white,
          leading: IconButton(
            onPressed: () {
              // BlocProvider.of<SearchCubit>(context).controller = TextEditingController();
              BlocProvider.of<SearchCubit>(context).resetFilters();
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.arrow_back_ios_rounded),
          ),
          title: Text(
            S.of(context).search,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          automaticallyImplyLeading: false,
          actions: [
            IconButton(
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (context) => const CartScreen(),));
                // Navigator.canPop(context) ? Navigator.pop(context) : null;
              },
              icon: Stack(
                children: [
                  const SizedBox(height: 60,width: 40,),
                  Positioned(top: 0,left: 0,child: Icon(Icons.shopping_cart,color: Constants.mainColor,size: 25,)),
                  Positioned(top: 0,right: 0,child: BlocBuilder<CartCubit, CartState>(
                    builder: (context, state) {
                      return CircleAvatar(radius: 9,backgroundColor: Colors.red,child: Text((context.read<CartCubit>().myCart?.data?.length ?? 0).toString(),style: const TextStyle(color: Colors.white,fontSize: 13,fontWeight: FontWeight.bold),),);
                    },
                  ))
                ],
              ),
            ),
          ],
          centerTitle: true,
        ),
        body: BlocBuilder<SearchCubit, SearchState>(
          builder: (context, state) {
            return Container(
              height: ScreenSizing.height,
              width: ScreenSizing.width,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Hero(
                      tag: 'search',
                      transitionOnUserGestures: true,
                      child: SearchBar(
                        controller: context.read<SearchCubit>().controller,
                        onSubmitted: (value) {
                          context.read<SearchCubit>().createSearchSuggestions(value);
                          context.read<SearchCubit>().getSearchedProducts();
                          Navigator.push(context, MaterialPageRoute(builder: (context) => const SearchSecond(),),);
                        },
                        backgroundColor: const WidgetStatePropertyAll<Color>(Colors.white),
                        leading: const Icon(Icons.search),
                        hintText: S.of(context).search_placeholder,
                        hintStyle: WidgetStateProperty.all(
                          TextStyle(color: Colors.grey[500], fontSize: 14),
                        ),
                        // padding: MaterialStateProperty.all(const EdgeInsets.symmetric(horizontal: 10, vertical: 10)),
                        shape: WidgetStatePropertyAll<OutlinedBorder?>(RoundedRectangleBorder(side: BorderSide(color: Colors.grey[300]!), borderRadius: BorderRadius.circular(40))),
                      ),
                    ),
                    const SizedBox(
                      height: 50,
                    ),
                    context.read<SearchCubit>().suggestedWords.isEmpty? Container():Row(
                      children: [
                        Text(
                          S.of(context).recent_searches,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        )
                      ],
                    ),
                    const SizedBox(
                      height: 10,
                    ),
                    TagsWidget(tags: context.read<SearchCubit>().suggestedWords,),

                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class TagsWidget extends StatelessWidget {
  final List<String> tags;

  const TagsWidget({super.key, required this.tags});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8.0, // Space between tags horizontally
      runSpacing: 4.0, // Space between rows of tags
      children: tags.map((tag) => InkWell(onTap: () {
        BlocProvider.of<SearchCubit>(context).setSearchWord(tag);
      },child: _buildTag(tag))).toList(),
    );
  }

  Widget _buildTag(String tag) {
    return Chip(
      label: Text(tag),
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8.0),
        side: BorderSide(color: Colors.grey[300]!)
      ),
    );
  }
}
