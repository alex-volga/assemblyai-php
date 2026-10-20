<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum PiiPolicy: string
{
    case BankingInformation = 'banking_information';
    case CreditCardCvv = 'credit_card_cvv';
    case CreditCardExpiration = 'credit_card_expiration';
    case CreditCardNumber = 'credit_card_number';
    case Date = 'date';
    case DateOfBirth = 'date_of_birth';
    case DriversLicense = 'drivers_license';
    case EmailAddress = 'email_address';
    case Location = 'location';
    case MedicalProcess = 'medical_process';
    case MedicalCondition = 'medical_condition';
    case Organization = 'organization';
    case PassportNumber = 'passport_number';
    case Password = 'password';
    case PersonAge = 'person_age';
    case PersonName = 'person_name';
    case PhoneNumber = 'phone_number';
    case PhysicalAddress = 'physical_address';
    case Pin = 'pin';
    case UsSocialSecurityNumber = 'us_social_security_number';
    case Url = 'url';
}
