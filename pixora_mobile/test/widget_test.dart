// This is a basic Flutter widget test.
import 'package:flutter_test/flutter_test.dart';
import 'package:pixora_mobile/main.dart';

void main() {
  testWidgets('App starts', (WidgetTester tester) async {
    await tester.pumpWidget(const PixoraApp());
  });
}
