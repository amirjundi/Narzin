import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/profile_cubits/profile_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';
import 'package:narzin/widgets/text_form_fields/custom_input_decorator.dart';

import '../../../generated/l10n.dart';

class AddAddressScreen extends StatefulWidget {
  const AddAddressScreen({super.key});

  @override
  State<AddAddressScreen> createState() => _AddAddressScreenState();
}

class _AddAddressScreenState extends State<AddAddressScreen> {
  @override
  void initState() {
    BlocProvider.of<ProfileCubit>(context).getDeliveryZones();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
        backgroundColor: Colors.white,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        title: Text(
          S.of(context).add_address,
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
      body: Container(
        height: ScreenSizing.height,
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // BlocBuilder<ProfileCubit, ProfileState>(
              //   builder: (context, state) {
              //     return CustomInputDecorator(
              //       title: S.of(context).location,
              //       suffix: IconButton(
              //         onPressed: () {
              //           context.read<ProfileCubit>().getCoordinates();
              //         },
              //         icon: const Icon(Icons.my_location_outlined),
              //       ),
              //       child: Padding(
              //         padding: const EdgeInsets.symmetric(horizontal: 8.0),
              //         child: Text(
              //           S.of(context).enter_location,
              //           style: const TextStyle(fontSize: 14, color: Colors.grey),
              //         ),
              //       ),
              //     );
              //   },
              // ),
              // const SizedBox(
              //   height: 10,
              // ),
              // Container(
              //   height: 200,
              //   width: ScreenSizing.width,
              //   decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey)),
              //   child: ClipRRect(
              //     borderRadius: BorderRadius.circular(20),
              //     child: const MapViewer(),
              //   ),
              // ),
              const SizedBox(
                height: 10,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return CustomTextFormField(
                    title: S.of(context).title,
                    hint: S.of(context).title_hint,
                    controller: context.read<ProfileCubit>().titleController,
                  );
                },
              ),
              const SizedBox(
                height: 10,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  var zones = context.read<ProfileCubit>().deliveryZones?.data ?? [];
                  var selectedZone = context.read<ProfileCubit>().selectedDeliveryZone;
                  return CustomInputDecorator(
                    title: 'Delivery Zone',
                    hint: 'Select your region',
                    child: DropdownButton<dynamic>(
                      items: zones
                          .map(
                            (e) => DropdownMenuItem<dynamic>(value: e, child: Text(e.name ?? '')),
                          )
                          .toList(),
                      isDense: true,
                      value: selectedZone,
                      hint: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                        child: Text(
                          'Select Delivery Zone',
                          style: TextStyle(color: Colors.grey[500], fontSize: 12),
                        ),
                      ),
                      isExpanded: true,
                      onChanged: (value) {
                        context.read<ProfileCubit>().setSelectedDeliveryZone(value);
                      },
                      underline: const SizedBox(),
                    ),
                  );
                },
              ),
              const SizedBox(
                height: 10,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return CustomTextFormField(
                    title: S.of(context).city,
                    hint: S.of(context).enter_city_name,
                    controller: context.read<ProfileCubit>().city,
                  );
                },
              ),
              const SizedBox(
                height: 10,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return CustomTextFormField(
                    title: S.of(context).address,
                    hint: S.of(context).full_address_hint,
                    controller: context.read<ProfileCubit>().fullAddress,
                  );
                },
              ),
              const SizedBox(
                height: 10,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return CustomTextFormField(
                    title: S.of(context).phone,
                    hint: S.of(context).phone_hint,
                    controller: context.read<ProfileCubit>().phone,
                  );
                },
              ),
              const SizedBox(
                height: 30,
              ),
              BlocBuilder<ProfileCubit, ProfileState>(
                builder: (context, state) {
                  return InkWell(
                    onTap: () {
                      context.read<ProfileCubit>().toggleIsDefault();
                    },
                    child: WalletUsageWidget(
                      label: S.of(context).deflt,
                      value: context.read<ProfileCubit>().isDefault,
                      onChanged: (value) {
                        context.read<ProfileCubit>().toggleIsDefault();
                      },
                    ),
                  );
                },
              ),
              const SizedBox(
                height: 10,
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20.0),
        child: SizedBox(
          height: 60,
          child: BlocBuilder<ProfileCubit, ProfileState>(
            builder: (context, state) {
              bool isLoading = context.read<ProfileCubit>().isLoading;
              return CustomSignIn_UpOne(
                title: S.of(context).save,
                customizeChild: isLoading
                    ? const Center(
                        child: CircularProgressIndicator(),
                      )
                    : Text(
                        S.of(context).save,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                ontap: isLoading
                    ? null
                    : () async {
                        String token = BlocProvider.of<LoginCubit>(context).loginModel?.data?.token ?? '';
                        var res = await context.read<ProfileCubit>().addAddress(token: token);
                        if (res == null) {
                          context.read<ProfileCubit>().getAddresses(token: token);
                          Navigator.pop(context);
                        }
                      },
              );
            },
          ),
        ),
      ),
    );
  }
}

class WalletUsageWidget extends StatelessWidget {
  WalletUsageWidget({
    super.key,
    this.value,
    this.label,
    this.amount,
    required this.onChanged,
  });

  final bool? value; // هل مختار ولا لأ
  final String? label; // اسم الخيار
  final double? amount; // القيمة/الرصيد

  final void Function(bool?) onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 10),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xffEEEFF2)),
      ),
      child: Row(
        children: [
          SizedBox(
            width: 21,
            height: 21,
            child: Checkbox(
              value: value ?? false,
              onChanged: onChanged,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(4),
              ),
              side: const BorderSide(color: Color(0xff6B7280)),
              activeColor: Theme.of(context).primaryColor,
            ),
          ),
          const SizedBox(width: 10),
          Spacer(),
          Text(
            label ?? S.of(context).wallet,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }
}
