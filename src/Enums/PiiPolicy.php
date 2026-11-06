<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum PiiPolicy: string
{
    case AccountNumber = 'account_number';
    case BankingInformation = 'banking_information';
    case BloodType = 'blood_type';
    case CreditCardCvv = 'credit_card_cvv';
    case CreditCardExpiration = 'credit_card_expiration';
    case CreditCardNumber = 'credit_card_number';
    case Date = 'date';
    case DateInterval = 'date_interval';
    case DateOfBirth = 'date_of_birth';
    case DriversLicense = 'drivers_license';
    case Drug = 'drug';
    case Duration = 'duration';
    case EmailAddress = 'email_address';
    case Event = 'event';
    case Filename = 'filename';
    case Gender = 'gender';
    case GenderSexuality = 'gender_sexuality';
    case HealthcareNumber = 'healthcare_number';
    case Injury = 'injury';
    case IpAddress = 'ip_address';
    case Language = 'language';
    case Location = 'location';
    case LocationAddress = 'location_address';
    case LocationAddressStreet = 'location_address_street';
    case LocationCity = 'location_city';
    case LocationCoordinate = 'location_coordinate';
    case LocationCountry = 'location_country';
    case LocationState = 'location_state';
    case LocationZip = 'location_zip';
    case MaritalStatus = 'marital_status';
    case MedicalCondition = 'medical_condition';
    case MedicalProcess = 'medical_process';
    case MoneyAmount = 'money_amount';
    case Nationality = 'nationality';
    case NumberSequence = 'number_sequence';
    case Occupation = 'occupation';
    case Organization = 'organization';
    case OrganizationMedicalFacility = 'organization_medical_facility';
    case PassportNumber = 'passport_number';
    case Password = 'password';
    case PersonAge = 'person_age';
    case PersonName = 'person_name';
    case PhoneNumber = 'phone_number';
    case PhysicalAttribute = 'physical_attribute';
    case PoliticalAffiliation = 'political_affiliation';
    case Religion = 'religion';
    case Sexuality = 'sexuality';
    case Statistics = 'statistics';
    case Time = 'time';
    case Url = 'url';
    case UsSocialSecurityNumber = 'us_social_security_number';
    case Username = 'username';
    case VehicleId = 'vehicle_id';
    case ZodiacSign = 'zodiac_sign';

    /**
     * @deprecated AssemblyAI uses location_address; mapped for backwards compatibility.
     */
    public const PhysicalAddress = self::LocationAddress;

    /**
     * @deprecated AssemblyAI uses number_sequence; mapped for backwards compatibility.
     */
    public const Pin = self::NumberSequence;
}
