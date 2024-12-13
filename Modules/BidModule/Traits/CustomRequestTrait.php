<?php

namespace Modules\BidModule\Traits;

use Illuminate\Support\Facades\DB;
use Modules\BidModule\Entities\CustomRequest;
use Modules\BidModule\Entities\CustomRequestBooking;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BookingModule\Entities\BookingOfflinePayment;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Events\BookingRequested;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\CartModule\Entities\Cart;
use Modules\PaymentModule\Entities\OfflinePayment;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ServiceManagement\Entities\Service;
use Modules\UserManagement\Entities\User;

trait CustomRequestTrait
{
    public function addCustomRequest($request, $service_address)
    {
        DB::beginTransaction();

        try {
            $custom_request = new CustomRequest();
            $custom_request->user_id = \is_null(auth('api')->user()->id) ? $request->guest_id : auth('api')->user()->id;
            $custom_request->user_address_id = $service_address;
            $custom_request->machine_name = $request->machine_name;
            $custom_request->description = $request->description;
            $custom_request->schedule_time = $request->schedule_time;
            $custom_request->no_seri = $request->no_seri;
            $custom_request->status = 'Pending';
            $custom_request->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['flag' => 'failed', 'message' => $th];
        }
        return ['flag' => 'success', 'custom_request_id' => $custom_request->id];
    }

    public function updateStatusCustomRequest($request, $id) {
        $custom_request = CustomRequest::find($id);

        DB::beginTransaction();
        try {
            $custom_request->status = $request->status;
            $custom_request->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['flag' => 'failed', 'message' => $th];
        }
        return ['flag' => 'success', 'custom_request_id' => $custom_request->id];
    }

    public function deleteCustomRequest(CustomRequest $custom_request) {
        DB::beginTransaction();
        try {
            $custom_request->delete();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['flag' => 'failed', 'message' => $th];
        }
        DB::commit();
        return ['flag' => 'success', 'custom_request_id' => $custom_request->id];
    }
    public function rejectCustom($note, $status, $id) {
        $custom_request = CustomRequest::find($id);

        DB::beginTransaction();
        try {
            $custom_request->status = $status;
            $custom_request->cancellation_note = $note;
            $custom_request->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['flag' => 'failed', 'message' => $th];
        }
        return ['flag' => 'success', 'custom_request_id' => $custom_request->id];
    }

    public function placeBookingRequest($userId, $request, $transactionId, int $isGuest = 0, $custom_request_id): array
    {
        $cartData = Cart::where(['customer_id' => $userId])->get();
        $user_service_address_id = CustomRequest::find($custom_request_id)->value('user_address_id');
        // \dd($user_service_address_id);

        if ($cartData->count() == 0) {
            return ['flag' => 'failed', 'message' => 'no data found'];
        }

        $isPartials = $request['is_partial'] ? 1 : 0;
        $customerWalletBalance = User::find($userId)?->wallet_balance;
        if ($isPartials && $isGuest && ($customerWalletBalance <= 0 || $customerWalletBalance >= $cartData->sum('total_cost'))) {
            return ['flag' => 'failed', 'message' => 'Invalid data'];
        }

        $bookingIds = [];
        foreach ($cartData->pluck('sub_category_id')->unique() as $subCategory) {

            $booking = new Booking();

            DB::transaction(function () use ($subCategory, $booking, $transactionId, $request, $cartData, $userId, $isGuest, $isPartials, $customerWalletBalance, $user_service_address_id) {
                $cartData = $cartData->where('sub_category_id', $subCategory);

                // if ($request->has('payment_method') && $request['payment_method'] == 'cash_after_service') {
                //     $transactionId = 'cash-payment';

                // } else if ($request->has('payment_method') && $request['payment_method'] == 'wallet_payment') {
                //     $transactionId = 'wallet-payment';
                // }

                $totalBookingAmount = $cartData->sum('total_cost');

                $bookingAdditionalChargeStatus = business_config('booking_additional_charge', 'booking_setup')->live_values ?? 0;
                $extraFee = 0;
                if ($bookingAdditionalChargeStatus) {
                    $extraFee = business_config('additional_charge_fee_amount', 'booking_setup')->live_values ?? 0;
                }
                $totalBookingAmount += $extraFee;

                $booking->customer_id = $userId;
                $booking->provider_id = $cartData->first()->provider_id;
                $booking->category_id = $cartData->first()->category_id;
                $booking->sub_category_id = $subCategory;
                $booking->zone_id = config('zone_id') == null ? $request['zone_id'] : config('zone_id');
                $booking->booking_status = 'accepted';
                $booking->is_paid = 0;
                $booking->payment_method = 'cash_after_service';
                $booking->transaction_id = $transactionId;
                $booking->total_booking_amount = $totalBookingAmount;
                $booking->total_tax_amount = $cartData->sum('tax_amount');
                $booking->total_discount_amount = $cartData->sum('discount_amount');
                $booking->total_campaign_discount_amount = $cartData->sum('campaign_discount');
                $booking->total_coupon_discount_amount = $cartData->sum('coupon_discount');
                $booking->coupon_code = $cartData->first()->coupon_code;
                $booking->service_schedule = date('Y-m-d H:i:s', strtotime($request['service_schedule'])) ?? now()->addHours(5);
                $booking->service_address_id = $user_service_address_id ?? '';
                $booking->booking_otp = rand(100000, 999999);
                $booking->is_guest = $isGuest;
                $booking->extra_fee = $extraFee;
                $booking->no_seri = $request->no_seri;
                $booking->save();

                foreach ($cartData->all() as $datum) {
                    $detail = new BookingDetail();
                    $detail->booking_id = $booking->id;
                    $detail->service_id = $datum['service_id'];
                    $detail->service_name = Service::find($datum['service_id'])->name ?? 'service-not-found';
                    $detail->variant_key = $datum['variant_key'];
                    $detail->quantity = $datum['quantity'];
                    $detail->service_cost = $datum['service_cost'];
                    $detail->discount_amount = $datum['discount_amount'];
                    $detail->campaign_discount_amount = $datum['campaign_discount'];
                    $detail->overall_coupon_discount_amount = $datum['coupon_discount'];
                    $detail->tax_amount = $datum['tax_amount'];
                    $detail->total_cost = $datum['total_cost'];
                    $detail->save();

                    $bookingDetailsAmount = new BookingDetailsAmount();
                    $bookingDetailsAmount->booking_details_id = $detail->id;
                    $bookingDetailsAmount->booking_id = $booking->id;
                    $bookingDetailsAmount->service_unit_cost = $datum['service_cost'];
                    $bookingDetailsAmount->service_quantity = $datum['quantity'];
                    $bookingDetailsAmount->service_tax = $datum['tax_amount'];
                    $bookingDetailsAmount->discount_by_admin = $this->calculate_discount_cost($datum['discount_amount'])['admin'];
                    $bookingDetailsAmount->discount_by_provider = $this->calculate_discount_cost($datum['discount_amount'])['provider'];
                    $bookingDetailsAmount->campaign_discount_by_admin = $this->calculate_campaign_cost($datum['campaign_discount'])['admin'];
                    $bookingDetailsAmount->campaign_discount_by_provider = $this->calculate_campaign_cost($datum['campaign_discount'])['provider'];
                    $bookingDetailsAmount->coupon_discount_by_admin = $this->calculate_coupon_cost($datum['coupon_discount'])['admin'];
                    $bookingDetailsAmount->coupon_discount_by_provider = $this->calculate_coupon_cost($datum['coupon_discount'])['provider'];
                    $bookingDetailsAmount->save();
                }

                $schedule = new BookingScheduleHistory();
                $schedule->booking_id = $booking->id;
                $schedule->changed_by = $userId;
                $schedule->is_guest = $isGuest;
                $schedule->schedule = date('Y-m-d H:i:s', strtotime($request['service_schedule'])) ?? now()->addHours(5);
                $schedule->save();

                $statusHistory = new BookingStatusHistory();
                $statusHistory->changed_by = $booking->id;
                $statusHistory->booking_id = $userId;
                $statusHistory->is_guest = $isGuest;
                $statusHistory->booking_status = isset($booking->provider_id) ? 'accepted' : 'pending';
                $statusHistory->save();

                if ($booking->booking_partial_payments->isNotEmpty()) {
                    if ($booking['payment_method'] == 'cash_after_service') {
                        placeBookingTransactionForPartialCas($booking);  // waller + CAS payment
                    } elseif ($booking['payment_method'] != 'wallet_payment') {
                        placeBookingTransactionForPartialDigital($booking);  //wallet + digital payment
                    }
                } elseif ($booking['payment_method'] == 'offline_payment') {
                    $customerInformation = (array)json_decode(base64_decode($request['customer_information']))[0];
                    $bookingOfflinePayment = new BookingOfflinePayment();
                    $bookingOfflinePayment->booking_id = $booking->id;
                    $bookingOfflinePayment->method_name = OfflinePayment::find($request['offline_payment_id'])?->method_name;
                    $bookingOfflinePayment->customer_information = $customerInformation;
                    $bookingOfflinePayment->save();
                } elseif ($booking['payment_method'] != 'cash_after_service' && $booking['payment_method'] != 'wallet_payment') {
                    placeBookingTransactionForDigitalPayment($booking);  //digital payment
                } elseif ($booking['payment_method'] != 'cash_after_service') {
                    placeBookingTransactionForWalletPayment($booking);   //wallet payment
                }

                $maximumBookingAmount = (business_config('max_booking_amount', 'booking_setup'))?->live_values;

                $bookingNotificationStatus = business_config('booking', 'notification_settings')->live_values;

                if ($booking->payment_method == 'cash_after_service') {
                    if ($maximumBookingAmount > 0 && $booking->total_booking_amount < $maximumBookingAmount) {
                        if (isset($booking->provider_id)) {
                            $provider = Provider::with('owner')->whereId($booking->provider_id)->first();
                            $fcmToken = $provider?->owner->fcm_token ?? null;
                            $languageKey = $provider?->owner?->current_language_key;
                            if (!is_null($fcmToken) && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) {
                                $title = get_push_notification_message('booking_accepted', 'provider_notification', $languageKey);
                                if ($title) {
                                    device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                                }
                            }
                        } else {
                            $providerIds = SubscribedService::where('sub_category_id', $subCategory)->ofSubscription(1)->pluck('provider_id')->toArray();
                            if (business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                                $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking?->zone_id)->where('is_suspended', 0)->get();
                            } else {
                                $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking?->zone_id)->get();
                            }

                            foreach ($providers as $provider) {
                                $fcmToken = $provider->owner->fcm_token ?? null;
                                $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', $provider?->owner?->current_language_key);
                                if (!is_null($fcmToken) && $provider?->service_availability && $title && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                            }
                        }
                    }
                } else {
                    if (isset($booking->provider_id)) {
                        $provider = Provider::with('owner')->whereId($booking->provider_id)->first();
                        $fcmToken = $provider?->owner?->fcm_token ?? null;
                        $languageKey = $provider?->owner?->current_language_key;
                        if (!is_null($fcmToken)) {
                            $title = get_push_notification_message('booking_accepted', 'provider_notification', $languageKey);
                            if ($title && $fcmToken && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) {
                                device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                            }
                        }
                    } else {
                        $providerIds = SubscribedService::where('sub_category_id', $subCategory)->ofSubscription(1)->pluck('provider_id')->toArray();
                        if (business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values) {
                            $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking->zone_id)->where('is_suspended', 0)->get();
                        } else {
                            $providers = Provider::with('owner')->whereIn('id', $providerIds)->where('zone_id', $booking->zone_id)->get();
                        }
                        foreach ($providers as $provider) {
                            $fcmToken = $provider->owner->fcm_token ?? null;
                            $title = get_push_notification_message('new_service_request_arrived', 'provider_notification', $provider?->owner?->current_language_key);
                            if (!is_null($fcmToken) && $provider?->service_availability && $title && isset($bookingNotificationStatus) && $bookingNotificationStatus['push_notification_booking']) device_notification($fcmToken, $title, null, null, $booking->id, 'booking');
                        }
                    }
                }
            });
            $bookingIds[] = $booking->id;
        }

        cart_clean($userId);
        event(new BookingRequested($booking));
        // dd($booking->id);
        return [
            'flag' => 'success',
            'booking_id' => $bookingIds,
            'readable_id' => $booking->readable_id
        ];
    }

    private function calculate_discount_cost(float $discount_amount): array
    {
        $data = BusinessSettings::where('settings_type', 'promotional_setup')->where('key_name', 'discount_cost_bearer')->first();
        if (!isset($data)) return [];
        $data = $data->live_values;

        if ($data['admin_percentage'] == 0) {
            $adminPercentage = 0;
        } else {
            $adminPercentage = ($discount_amount * $data['admin_percentage']) / 100;
        }

        if ($data['provider_percentage'] == 0) {
            $providerPercentage = 0;
        } else {
            $providerPercentage = ($discount_amount * $data['provider_percentage']) / 100;
        }
        return [
            'admin' => $adminPercentage,
            'provider' => $providerPercentage
        ];
    }

    /**
     * @param float $campaignAmount
     * @return array
     */
    private function calculate_campaign_cost(float $campaignAmount): array
    {
        $data = BusinessSettings::where('settings_type', 'promotional_setup')->where('key_name', 'campaign_cost_bearer')->first();
        if (!isset($data)) return [];
        $data = $data->live_values;

        if ($data['admin_percentage'] == 0) {
            $adminPercentage = 0;
        } else {
            $adminPercentage = ($campaignAmount * $data['admin_percentage']) / 100;
        }

        if ($data['provider_percentage'] == 0) {
            $providerPercentage = 0;
        } else {
            $providerPercentage = ($campaignAmount * $data['provider_percentage']) / 100;
        }

        return [
            'admin' => $adminPercentage,
            'provider' => $providerPercentage
        ];
    }

    /**
     * @param float $couponAmount
     * @return array
     */
    private function calculate_coupon_cost(float $couponAmount): array
    {
        $data = BusinessSettings::where('settings_type', 'promotional_setup')->where('key_name', 'coupon_cost_bearer')->first();
        if (!isset($data)) return [];
        $data = $data->live_values;

        if ($data['admin_percentage'] == 0) {
            $adminPercentage = 0;
        } else {
            $adminPercentage = ($couponAmount * $data['admin_percentage']) / 100;
        }

        if ($data['provider_percentage'] == 0) {
            $providerPercentage = 0;
        } else {
            $providerPercentage = ($couponAmount * $data['provider_percentage']) / 100;
        }

        return [
            'admin' => $adminPercentage,
            'provider' => $providerPercentage
        ];
    }

    /**
     * @param $booking
     * @param float $bookingAmount
     * @param $providerId
     * @return void
     */
    private function update_admin_commission($booking, float $bookingAmount, $providerId): void
    {
        $serviceCost = $booking['total_booking_amount'] - $booking['total_tax_amount'] + $booking['total_discount_amount'] + $booking['total_campaign_discount_amount'] + $booking['total_coupon_discount_amount'] - $booking['extra_fee'];

        $bookingDetailsAmounts = BookingDetailsAmount::where('booking_id', $booking->id)->get();
        $promotionalCostByAdmin = 0;
        $promotionalCostByProvider = 0;
        foreach ($bookingDetailsAmounts as $bookingDetailsAmount) {
            $promotionalCostByAdmin += $bookingDetailsAmount['discount_by_admin'] + $bookingDetailsAmount['coupon_discount_by_admin'] + $bookingDetailsAmount['campaign_discount_by_admin'];
            $promotionalCostByProvider += $bookingDetailsAmount['discount_by_provider'] + $bookingDetailsAmount['coupon_discount_by_provider'] + $bookingDetailsAmount['campaign_discount_by_provider'];
        }

        $providerReceivableTotalAmount = $serviceCost - $promotionalCostByProvider;

        $provider = Provider::find($booking['provider_id']);
        $commissionPercentage = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
        $adminCommission = ($providerReceivableTotalAmount * $commissionPercentage) / 100;

        $adminCommissionWithoutCost = $adminCommission - $promotionalCostByAdmin;

        $bookingAmountWithoutCommission = $booking['total_booking_amount'] - $adminCommissionWithoutCost;

        $bookingAmountDetailAmount = BookingDetailsAmount::where('booking_id', $booking->id)->first();
        $bookingAmountDetailAmount->admin_commission = $adminCommission;
        $bookingAmountDetailAmount->provider_earning = $bookingAmountWithoutCommission;
        $bookingAmountDetailAmount->save();
    }



    //=============== REFERRAL EARN & LOYALTY POINT ===============

    /**
     * @param $userId
     * @return false|void
     */
    private function referral_earning_calculation($userId)
    {
        $isFirstBooking = Booking::where('customer_id', $userId)->count('id');
        if ($isFirstBooking > 1) return false;

        $referredByUser = User::find($userId)->referred_by_user ?? null;
        if (is_null($referredByUser)) return false;

        $customerReferralEarning = business_config('customer_referral_earning', 'customer_config')->live_values ?? 0;
        $amount = business_config('referral_value_per_currency_unit', 'customer_config')->live_values ?? 0;

        if ($customerReferralEarning == 1) {
            referralEarningTransactionAfterBookingComplete($referredByUser, $amount);
            $user = User::where('id', $userId)->first();
            $title = with_currency_symbol($amount) . ' ' . get_push_notification_message('referral_earning', 'customer_notification', $user?->current_language_key);
            if ($title && $user->fcm_token) {
                device_notification($user->fcm_token, $title, null, null, null, 'wallet', null, $user->id);
            }
        }
    }

    /**
     * @param $userId
     * @param $bookingAmount
     * @return false|void
     */
    private function loyaltyPointCalculation($userId, $bookingAmount)
    {

        $customerLoyaltyPoint = business_config('customer_loyalty_point', 'customer_config');
        if (isset($customerLoyaltyPoint) && $customerLoyaltyPoint->live_values != '1') return false;

        $percentagePerBooking = business_config('loyalty_point_percentage_per_booking', 'customer_config');
        $pointAmount = ($percentagePerBooking->live_values * $bookingAmount) / 100;

        $pointPerCurrencyUnit = business_config('loyalty_point_value_per_currency_unit', 'customer_config');

        $point = $pointPerCurrencyUnit->live_values * $pointAmount;

        loyaltyPointTransaction($userId, $point);

        $user = User::where('id', $userId)->first();
        $title = $point . ' ' . get_push_notification_message('loyalty_point', 'customer_notification', $user?->current_language_key);
        $dataInfo = [
            'user_name' => $user?->first_name . ' ' . $user?->last_name,
        ];
        if ($title && $user && $user->is_active && $user->fcm_token) {
            device_notification($user->fcm_token, $title, null, null, null, 'loyalty_point', null, $user->id, $dataInfo);
        }
    }

    public function customRequestBooking($booking_id, $custom_request_id)
    {
        $create = CustomRequestBooking::create([
            'booking_id' => $booking_id,
            'custom_request_id' => $custom_request_id
        ]);
    }
}
