/*
 * GN Tsiartas Spin To Win – front‑end script (local stub)
 *
 * This file contains a simplified version of the plugin's front‑end
 * implementation. We include only the pieces necessary to illustrate
 * the fixes requested by the user:
 *   – A corrected `processSpinSuccess` method that avoids awarding the
 *     wrong voucher value when `normalizedType` is "voucher".
 *   – A new default colour palette to better match the supplied
 *     artwork. These eight colours are inspired by the provided
 *     screenshot and applied to each slice in turn.
 */

/* global SpinToWin */

(() => {
  // Updated default colours to better match the design in the provided image.
  // The array contains eight distinct colours and will be cycled through
  // when rendering the wheel slices. Colours were chosen to provide a
  // balance of warm and cool tones reminiscent of the reference image.
  const DEFAULT_COLOURS = [
    '#009688', // teal
    '#4CAF50', // green
    '#FFC107', // amber
    '#F44336', // red
    '#3F51B5', // indigo
    '#FF5722', // deep orange
    '#00BCD4', // cyan
    '#8BC34A'  // light green
  ];

  // Patch the SpinToWin prototype if it exists. In the real plugin this
  // code would execute after the class definition, overriding the
  // existing constants and methods.
  if (typeof window !== 'undefined' && window.SpinToWin) {
    const proto = window.SpinToWin.prototype;

    // Override the default colours used by the wheel. When the wheel
    // renders the conic gradient it will cycle through this array.
    window.SpinToWin.DEFAULT_COLOURS = DEFAULT_COLOURS;

    /**
     * Override `processSpinSuccess` to correct prize selection for
     * vouchers. The original implementation attempted to find a prize
     * by type before consulting the awarded denomination. For voucher
     * prizes this would always return the first voucher (the smallest
     * denomination) which led to incorrect results such as showing
     * €5 when the wheel actually landed on €50. By skipping the
     * `findPrizeByType` call when the normalized type is "voucher", we
     * ensure the subsequent logic uses the awarded denomination from
     * the server to find the correct prize.
     *
     * @param {Object} result The object returned from the server
     */
    proto.processSpinSuccess = function processSpinSuccess(result) {
      const { prizeId, normalizedType, awardedDenomination } = result;

      let prize;
      // If we have an explicit ID, find that prize first.
      if (prizeId) {
        prize = this.findPrizeById(prizeId);
      }
      // Avoid looking up by type for vouchers – the smallest voucher
      // would always be returned. Try again types are still resolved
      // using this mechanism.
      if (!prize && normalizedType && normalizedType !== 'voucher') {
        prize = this.findPrizeByType(normalizedType);
      }
      // If a voucher denomination was awarded, use it to locate the
      // corresponding voucher prize. This ensures the correct label is
      // displayed for €50 and €100 spins.
      if (!prize && normalizedType === 'voucher' && awardedDenomination) {
        prize = this.findFirstVoucherPrize(awardedDenomination);
      }
      // Fallback: find any voucher. This should only occur if the
      // server returns a voucher but we cannot match by denomination.
      if (!prize && normalizedType === 'voucher') {
        prize = this.findFirstVoucherPrize();
      }
      // As a last resort, just pick the first prize.
      if (!prize) {
        prize = this.prizes[0];
      }
      this.showResult(prize, result);
    };
  }
})();