FormStackAPI
============

This is lightweight PHP wrapper for FormStackAPI: https://www.formstack.com/developers/, requires PHP 5.6+.

## What is FormStack?
From www.formstack.com 
> Advanced online forms for easy digital engagement.
> Easily build powerful forms without any coding knowledge. Just drag & drop the fields you need and create forms instantly.

## What can the API be used for

One example is using with an CRM/ERP or similar system that does not have an easy way for non-technical people to create forms that interact with your data. You can use the API to integrate FormStack and your data. FormStack also has Webhooks that can call on a web page and send the data via HTTP POST.

## 1.1 Added Features
Load a form to
 
- Retrieve Webhooks
- Add Webhooks
- Update Webhooks
- Delete Webhooks

## 1.0 Features
- Retrieve forms
- Get all form fields
- Get one/all submissions
- Search submissions
- Update submissions

### Note 
- 1.1 does not support all the features of the FormStack API, such as Confirmations and Notifications
- FormStack has API rate limits per day
- Create a test form in FormStack to run your tests on

