import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:insta_image_viewer/insta_image_viewer.dart';
import 'package:narzin/generated/assets.dart';

class ImagePreviewer extends StatelessWidget {
  const ImagePreviewer({super.key,required this.imageUrl,
    this.errorImage,
  });

  final String imageUrl;
  final String? errorImage;
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(backgroundColor: Colors.transparent,automaticallyImplyLeading: false,leading: IconButton(onPressed: () {
        Navigator.canPop(context)?Navigator.pop(context):null;
      },icon: Icon(Icons.close,color: Colors.white,size: 45,),),),
      backgroundColor: Colors.black87,
      body: Center(
        child: ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: CachedNetworkImage(imageUrl: imageUrl,
            fit: BoxFit.contain,
            placeholder:(context, url) =>  const Center(child: CircularProgressIndicator(),),
            errorWidget: (context, url, error) =>errorImage == null? Container():Image.asset(
              errorImage??Assets.imagesProductPlaceholder2,
              fit: BoxFit.contain,
            ),),
        ),
      ),
    );
  }
}
