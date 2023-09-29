




# BALE-2
UPDATE postalCodes SET setUpRate = 5000000000000000, transportRate = 5000000000000000, treatmentRate = 5000000000000000, traceabilityRate = 5000000000000000 WHERE deleted IS NULL AND region_id = 4 AND zone LIKE '2';

# BALE-3
UPDATE postalCodes SET setUpRate = 13000000000000000, transportRate = 13000000000000000, treatmentRate = 13000000000000000, traceabilityRate = 13000000000000000 WHERE deleted IS NULL AND  region_id = 4 AND zone LIKE '3';

# BALE-4
UPDATE postalCodes SET setUpRate = 21000000000000000, transportRate = 21000000000000000, treatmentRate = 21000000000000000, traceabilityRate = 21000000000000000 WHERE deleted IS NULL AND  region_id = 4 AND zone LIKE '4';


# GENEVE-1
UPDATE postalCodes SET setUpRate = -2000000000000000, transportRate = -1000000000000000, treatmentRate = 0, traceabilityRate = 0 WHERE deleted IS NULL AND  region_id = 8 AND zone LIKE '1';

# GENEVE-2
UPDATE postalCodes SET setUpRate = 4000000000000000, transportRate = 4000000000000000, treatmentRate = 5000000000000000, traceabilityRate = 6000000000000000 WHERE deleted IS NULL AND  region_id = 8 AND zone LIKE '2';

# GENEVE-3
UPDATE postalCodes SET setUpRate = 9000000000000000, transportRate = 9000000000000000, treatmentRate = 10000000000000000, traceabilityRate = 11000000000000000 WHERE deleted IS NULL AND  region_id = 8 AND zone LIKE '3';

# GENEVE-4
UPDATE postalCodes SET setUpRate = 18000000000000000, transportRate = 18000000000000000, treatmentRate = 18000000000000000, traceabilityRate = 19000000000000000 WHERE deleted IS NULL AND  region_id = 8 AND zone LIKE '4';


#LUCERNE-1
UPDATE postalCodes SET setUpRate = -2000000000000000, transportRate = -1000000000000000, treatmentRate = 0, traceabilityRate = 0 WHERE deleted IS NULL AND  region_id = 5 AND zone LIKE '1';

#LUCERNE-2
UPDATE postalCodes SET setUpRate = 4000000000000000, transportRate = 4000000000000000, treatmentRate = 5000000000000000, traceabilityRate = 6000000000000000 WHERE deleted IS NULL AND  region_id = 5 AND zone LIKE '2';

#LUCERNE-3
UPDATE postalCodes SET setUpRate = 9000000000000000, transportRate = 9000000000000000, treatmentRate = 10000000000000000, traceabilityRate = 11000000000000000 WHERE deleted IS NULL AND  region_id = 5 AND zone LIKE '3';

#LUCERNE-4
UPDATE postalCodes SET setUpRate = 18000000000000000, transportRate = 18000000000000000, treatmentRate = 18000000000000000, traceabilityRate = 19000000000000000 WHERE deleted IS NULL AND  region_id = 5 AND zone LIKE '4';



#ZURICH-1
UPDATE postalCodes SET setUpRate = -2000000000000000, transportRate = -1000000000000000, treatmentRate = 0, traceabilityRate = 0 WHERE deleted IS NULL AND  region_id = 6 AND zone LIKE '1';

#ZURICH-2
UPDATE postalCodes SET setUpRate = 4000000000000000, transportRate = 4000000000000000, treatmentRate = 5000000000000000, traceabilityRate = 6000000000000000 WHERE deleted IS NULL AND  region_id = 6 AND zone LIKE '2';


#ZURICH-3
UPDATE postalCodes SET setUpRate = 9000000000000000, transportRate = 9000000000000000, treatmentRate = 10000000000000000, traceabilityRate = 11000000000000000 WHERE deleted IS NULL AND  region_id = 6 AND zone LIKE '3';

#ZURICH-4
UPDATE postalCodes SET setUpRate = 18000000000000000, transportRate = 18000000000000000, treatmentRate = 18000000000000000, traceabilityRate = 19000000000000000 WHERE deleted IS NULL AND  region_id = 6 AND zone LIKE '4';