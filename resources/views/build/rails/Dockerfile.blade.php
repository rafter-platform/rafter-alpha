FROM ruby:latest

# Install yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update -qq && apt-get install -y build-essential nodejs yarn

ENV APP_HOME /app
RUN mkdir $APP_HOME
WORKDIR $APP_HOME

# Install bundler and the current bundle
RUN gem install bundler:2.1.2
ADD Gemfile* $APP_HOME/
RUN bundle install

ADD . $APP_HOME

# Run Yarn Install
RUN yarn install --check-files

# Precompile assets
RUN RAILS_ENV=production bundle exec rake assets:precompile

RUN chmod 755 docker-entrypoint.sh
ENTRYPOINT ["/app/docker-entrypoint.sh"]
