import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:printerproject/services/Authentication.dart';
import 'package:printerproject/Services/global.dart';
import 'package:printerproject/widgets/Roundedbutton.dart';
import 'package:printerproject/screens/Home.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  String _email = '';
  String _password = '';
  loginPressed() async {
    if (_email.isEmpty || _password.isEmpty) {
      errorSnackBar(context, 'Please enter all required fields');
      return;
    }

    try {
      http.Response response = await AuthServices.login(_email, _password);

      print('Response code: ${response.statusCode}');
      print('Response body: ${response.body}');

      if (response.statusCode == 200) {
        // Decode the response JSON
        Map<String, dynamic> responseMap = jsonDecode(response.body);

        // Save user info to SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('userEmail', responseMap['user']['email']);
        await prefs.setString('userName', responseMap['user']['name']);
        await prefs.setInt('userId', responseMap['user']['id']);

        // If your backend returns a token, save it here (optional)
        if (responseMap.containsKey('token')) {
          await prefs.setString('token', responseMap['token']);
        }

        // Navigate to the Home Screen and remove the login page from stack
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => HomeScreen()),
        );
      } else {
        // Handle errors gracefully
        Map<String, dynamic> responseMap = jsonDecode(response.body);
        errorSnackBar(context, responseMap['message'] ?? 'Login failed');
      }
    } catch (e) {
      errorSnackBar(context, 'An error occurred: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.background,
      body: SingleChildScrollView(
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                alignment: Alignment.center,
                child: Image(
                  height: 300,
                  width: 300,
                  image: AssetImage(
                      'lib/images/hifrlogo.png'), // Adjust the logo path
                ),
              ),
              const SizedBox(height: 5),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Text(
                  'Hidramani',
                  style: TextStyle(
                    fontSize: 22,
                    color: Theme.of(context).textTheme.displayLarge!.color,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: const Text(
                  'Login to Here',
                  style: TextStyle(
                    fontSize: 15,
                    color: Color.fromARGB(255, 149, 148, 148),
                  ),
                ),
              ),
              const SizedBox(height: 30),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: const Text(
                  'Email:',
                  style: TextStyle(
                    fontSize: 15,
                    color: Color.fromARGB(255, 221, 130, 10),
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                height: 50,
                child: TextField(
                  decoration: const InputDecoration(
                    hintText: 'Enter your email',
                    hintStyle: TextStyle(
                      color: Color.fromARGB(255, 149, 148, 148),
                    ),
                    border: OutlineInputBorder(),
                  ),
                  onChanged: (value) {
                    setState(() {
                      _email = value;
                    });
                  },
                ),
              ),
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: const Text(
                  'Password:',
                  style: TextStyle(
                    fontSize: 15,
                    color: Color.fromARGB(255, 221, 130, 10),
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                height: 50,
                child: TextField(
                  obscureText: true,
                  decoration: const InputDecoration(
                    hintText: 'Enter your password',
                    hintStyle: TextStyle(
                      color: Color.fromARGB(255, 149, 148, 148),
                    ),
                    border: OutlineInputBorder(),
                  ),
                  onChanged: (value) {
                    setState(() {
                      _password = value;
                    });
                  },
                ),
              ),
              const SizedBox(height: 50),
              // Login button
              Center(
                child: RoundedButton(
                  btnText: 'LOG IN',
                  onBtnPressed: () => loginPressed(),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
