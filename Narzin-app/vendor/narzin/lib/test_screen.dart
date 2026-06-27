import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/widgets/app_infrastructure_widgets/product_item_widget.dart';

class TestScreen extends StatelessWidget {
  const TestScreen({super.key});
  final String? product_image = 'https://s3-alpha-sig.figma.com/img/dcb4/3283/bd2beb7b7955ad34ed519ba8683d54cb?Expires=1734307200&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=XmB13q9RR3n2DL1UN1AeOoKvd90brqZCjqqwcP3OfWZFLVSjhpQsdUjD--P2db~Gs3fKI7YuaeaqLieiKPJzMD3Xhv2zdbY9PYTfNq6nqyhFYUC9Lj~ssw3dudQPwVtY~GNI-1kJ7z6xcj2IEZVGQ24oemoNI78L~mJ2Zs7MVJ55~wqfr2fmBL0~NdsyhDSRWtHkk-UoB~-MFMcCagbSqTomwq7hU2w~i9d9pZcwTivsBmyw6A3GF~JdLT3JvGwY5wfHFwqepp2K7qZTrhWx2lR8J~P0gWjVHqoZZ3b8RT5gC54-TO24gDiL3CSLWnATnH27jdlJWzosNMWSV6YIJw__';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Container(
          width: ScreenSizing.width,
          height: ScreenSizing.height,
          child: Column(
            children: [
              ProductItem(
                productImage: null,
                productName: "Test productsssssss",
                icon: Icons.favorite,
                priceFrom: '24.0',
                priceTo: '20.11',
                onPressed: () {

              },),
            ],
          ),
        ),
      ),
    );
  }
}


