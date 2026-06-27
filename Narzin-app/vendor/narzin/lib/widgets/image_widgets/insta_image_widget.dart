import 'dart:io';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:fullscreen_image_viewer/fullscreen_image_viewer.dart' as viewer;
import 'package:insta_image_viewer/insta_image_viewer.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/widgets/image_widgets/image_previewer.dart';

class InstaNetworkImageWidget2 extends StatelessWidget {
  const InstaNetworkImageWidget2({
    super.key,
    required this.imageUrl,
    this.errorImage,
  });

  final String imageUrl;
  final String? errorImage;

  @override
  Widget build(BuildContext context) {
    return InstaImageViewer(
      child: CachedNetworkImage(imageUrl: imageUrl,
        fit: BoxFit.contain,
        placeholder:(context, url) =>  const Center(child: CircularProgressIndicator(),),
        errorWidget: (context, url, error) =>errorImage == null? Container():Image.asset(
          errorImage??Assets.imagesProductPlaceholder2,
          fit: BoxFit.contain,
        ),),
    );
  }
}

class InstaNetworkImageWidget3 extends StatelessWidget {
  const InstaNetworkImageWidget3({
    super.key,
    required this.imageUrl,
    this.errorImage,
  });

  final String imageUrl;
  final String? errorImage;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (context) => ImagePreviewer(imageUrl:imageUrl,errorImage:errorImage),));
      },
      child: CachedNetworkImage(imageUrl: imageUrl,
        fit: BoxFit.cover,
        placeholder:(context, url) =>  const Center(child: CircularProgressIndicator(),),
        errorWidget: (context, url, error) =>errorImage == null? Container():Image.asset(
          errorImage??Assets.imagesProductPlaceholder2,
          fit: BoxFit.cover,
        ),),
    );
  }
}

class InstaNetworkImageWidget extends StatelessWidget {
  const InstaNetworkImageWidget({
    super.key,
    required this.imageUrl,
    this.errorImage,
  });

  final String imageUrl;
  final String? errorImage;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () async {
        await viewer.FullscreenImageViewer.open(
          context: context,
          child: ImagePreviewer(imageUrl: imageUrl,),
        );
      },
      child: CachedNetworkImage(imageUrl: imageUrl,
        fit: BoxFit.cover,
        placeholder:(context, url) =>  const Center(child: CircularProgressIndicator(),),
        errorWidget: (context, url, error) =>errorImage == null? Container():Image.asset(
          errorImage??Assets.imagesProductPlaceholder2,
          fit: BoxFit.cover,
        ),),
    );
  }
}


class InstaFileImageWidget extends StatelessWidget {
  const InstaFileImageWidget({
    super.key,
    required this.image,
  });

  final File image;

  @override
  Widget build(BuildContext context) {
    return InstaImageViewer(

      child: Image.file( image,
        fit: BoxFit.contain,)

    );
  }
}
