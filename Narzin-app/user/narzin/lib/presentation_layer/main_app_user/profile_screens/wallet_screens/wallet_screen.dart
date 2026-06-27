import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/wallet_cubits/wallet_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:shimmer/shimmer.dart';

class WalletScreen extends StatelessWidget {
  const WalletScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).wallet,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
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
      body: BlocBuilder<WalletCubit, WalletState>(
        builder: (context, state) {
          bool isLoading = context.read<WalletCubit>().isLoading;
          return Container(
            height: ScreenSizing.height,
            width: ScreenSizing.width,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: isLoading
                ? const WalletShimmer()
                : SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        SizedBox(
                          height: ScreenSizing.height * 0.05,
                        ),
                        Center(
                            child: Text(
                          S.of(context).your_balance,
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 18, color: Colors.grey[800]),
                        )),
                        Center(
                          child: Text(
                            "EUR ${context.read<WalletCubit>().wallet?.data?.balance.toString() ?? 'XX'}",
                            style: TextStyle(fontSize: 30, color: Constants.mainColor, fontWeight: FontWeight.bold),
                            textAlign: TextAlign.center,
                          ),
                        ),
                        const SizedBox(
                          height: 30,
                        ),
                        Divider(
                          endIndent: 0,
                          indent: 0,
                          color: Colors.grey[300],
                        ),
                        const SizedBox(
                          height: 30,
                        ),
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemBuilder: (context, index) {
                            String amount = context.read<WalletCubit>().walletTransactions?.data?[index].amount.toString() ?? 'XX';
                            String type = context.read<WalletCubit>().walletTransactions?.data?[index].type.toString() ?? 'XX';
                            String date = context.read<WalletCubit>().walletTransactions?.data?[index].createdAt.toString() ?? 'XX';
                            DateTime processedDate = DateTime.tryParse(date) ?? DateTime.now();
                            return Container(
                              constraints: const BoxConstraints(
                                minHeight: 20,
                              ),
                              width: ScreenSizing.width,
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                              decoration: BoxDecoration(
                                border: Border.all(
                                  color: Colors.grey[200]!,
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          '${type == 'deposit'?S.of(context).added:S.of(context).withdrawn} EUR $amount ${type == 'deposit'?S.of(context).to_your_balance:S.of(context).from_your_balance}',
                                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500, color: Color(0xff090F24)),
                                        ),
                                      ),
                                      Text(
                                        "${processedDate.year}/${processedDate.month}/${processedDate.day}",
                                        style: const TextStyle(color: Color(0xff626262)),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(
                                    height: 15,
                                  ),
                                  Text(
                                    S.of(context).through_our_delivery_partner,
                                    style: const TextStyle(color: Color(0xff090F24), fontSize: 13, fontWeight: FontWeight.w300),
                                  )
                                ],
                              ),
                            );
                          },
                          separatorBuilder: (context, index) => const SizedBox(
                            height: 20,
                          ),
                          itemCount: context.read<WalletCubit>().walletTransactions?.data?.length??0,
                        ),
                      ],
                    ),
                  ),
          );
        },
      ),
    );
  }
}

class WalletShimmer extends StatelessWidget {
  const WalletShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Wallet Card Placeholder
          Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.white,
            child: Container(
              height: 180,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Wallet Balance Placeholder
          Shimmer.fromColors(
            baseColor: Colors.grey[300]!,
            highlightColor: Colors.white,
            child: Container(
              height: 30,
              width: 150,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Transaction List Placeholder
          Expanded(
            child: ListView.builder(
              itemCount: 5,
              itemBuilder: (context, index) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Row(
                  children: [
                    // Circular Icon Placeholder
                    Shimmer.fromColors(
                      baseColor: Colors.grey[300]!,
                      highlightColor: Colors.white,
                      child: Container(
                        width: 50,
                        height: 50,
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),

                    // Transaction Details Placeholder
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Shimmer.fromColors(
                            baseColor: Colors.grey[300]!,
                            highlightColor: Colors.white,
                            child: Container(
                              height: 20,
                              width: double.infinity,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Shimmer.fromColors(
                            baseColor: Colors.grey[300]!,
                            highlightColor: Colors.white,
                            child: Container(
                              height: 15,
                              width: 100,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
