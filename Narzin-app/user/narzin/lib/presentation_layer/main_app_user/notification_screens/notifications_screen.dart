import 'package:flutter/material.dart';
import 'package:narzin/core/screen_sizing_constants.dart';

import '../../../generated/l10n.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).notifications,
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
      body: Container(
        width:ScreenSizing.width,
        height: ScreenSizing.height,
        padding: const EdgeInsets.symmetric(horizontal: 10),
        child: ListView.separated(itemBuilder: (context, index) {
          return const Column(
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 35,
                  ),
                  Expanded(child: ListTile(
                    title: Text('data'),
                  ),),
                ],
              ),
            ],
          );
        }, separatorBuilder: (context, index) => const Divider(height: 30,indent: 10,), itemCount: 5),
      ),
    );
  }
}
