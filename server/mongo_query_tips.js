//daily stats aggregate by weekday and hour
db.getCollection('wc_stats').aggregate([
  {$group:
  {_id: { dw: '$local_day_of_week', hour: '$local_hour', wc_id: '$wc_id'},
    num_use: {$avg: '$number_of_use'},
    media_uso: { $avg: '$average_time'}
  }
  },
  {
    $sort: {
      '_id.dw': 1,
      '_id.hour': 1
    }
  }
]);

//to restrict to hour and day
db.getCollection('wc_stats').aggregate([
    {
      $match:
          {
              local_day_of_week: 0, //0-6 0 is sunday
              local_hour: 11,
              wc_id: 1
          }
    },
    {$group:
        {_id: { dw: '$local_day_of_week', hour: '$local_hour'},
            num_use: {$avg: '$number_of_use'},
            media_uso: { $avg: '$average_time'}
        }
    },
    {
        $sort: {
            '_id.dw': 1,
            '_id.hour': 1
        }
    }
]);

